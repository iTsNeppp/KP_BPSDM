<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;
use App\Models\File; 
use Illuminate\Support\Facades\Storage;
use App\Helpers\LogHelper;
use App\Models\EvaluationFormL1;
use App\Models\EvaluationFormL34;



class ParticipantController extends Controller
{
    /**
     * Tampilan Dashboard khusus Peserta
     */
    public function index()
    {
        if (Auth::user()->role !== 'participant') {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $currentYear = date('Y');

        // 1. Hitung total pelatihan yang pernah diikuti (seluruh waktu)
        $totalFollowed = \App\Models\Participant::where('nip_nik', $user->nip_nik)->count();

        // 2. Hitung Total JP Tahun Ini
        // Mengambil data dari tabel participant -> join ke training -> ambil kolom JP -> filter tahun berjalan
        $myJpThisYear = \App\Models\Participant::where('nip_nik', $user->nip_nik)
            ->whereHas('training', function($q) use ($currentYear) {
                $q->whereYear('tgl_mulai', $currentYear);
            })
            ->with('training')
            ->get()
            ->sum(function($p) {
                return $p->training->jp;
            });

        return view('participant.dashboard', compact('user', 'totalFollowed', 'myJpThisYear'));
    }

    /**
     * Menu: Daftar Pelatihan (Melihat semua pelatihan yang dibuka oleh Bidang)
     */
    public function availableTrainings(Request $request)
    {
        $search = $request->query('search');

        // Mengambil semua pelatihan dengan hitungan peserta
        $trainings = \App\Models\Training::withCount('participants')
            // Filter Pencarian jika ada
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_pelatihan', 'LIKE', "%$search%")
                    ->orWhere('bidang', 'LIKE', "%$search%")
                    ->orWhere('lokasi', 'LIKE', "%$search%");
                });
            })
            // Urutan: Active (tgl_selesai >= hari ini) di atas, Selesai di bawah
            ->orderByRaw("tgl_selesai < '" . now()->toDateString() . "' ASC")
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return view('participant.available_trainings', compact('trainings', 'search'));
    }

    /**
     * Halaman Lengkapi Profil (Muncul setelah login Google pertama kali)
     */
    public function completeProfile()
    {
        $user = Auth::user();
        return view('participant.complete_profile', compact('user'));
    }

    /**
     * Simpan Pelengkapan Profil
     */
    public function storeProfile(Request $request)
    {
        $user = \App\Models\User::findOrFail(Auth::id());

        $request->validate([
            'nip_nik' => 'required|unique:users,nip_nik,' . $user->id,
            'gender' => 'required',
            'jabatan' => 'required',
            'instansi' => 'required',
            'provinsi' => 'required',
            'kabupaten_kota' => 'required',
            'kecamatan' => 'required',
            'kelurahan' => 'required',
            'status_kepegawaian' => 'required',
            'whatsapp' => 'required'
        ]);

        $user->update($request->all());

        return redirect()->route('participant.dashboard')->with('success', 'Profil berhasil diperbarui.');
    }

    // Proses Daftar dengan Kode
    public function enroll(Request $request, $id)
    {
        $training = \App\Models\Training::findOrFail($id);
        $user = Auth::user();

        // Validasi Token
        if (strtoupper($request->invitation_code) !== strtoupper($training->invitation_code)) {
            return redirect()->back()->with('error', 'Kode Undangan Salah!');
        }

        // OTOMATIS COPY DATA DARI USER KE PARTICIPANT
        \App\Models\Participant::updateOrCreate(
            [
                'training_id' => $id,
                'nip_nik'     => $user->nip_nik // Kunci utama pencocokan
            ],
            [
                'user_id'            => $user->id,
                'name'               => $user->name,
                'gender'             => $user->gender,
                'jabatan'            => $user->jabatan,
                'instansi'           => $user->instansi,
                'provinsi'           => $user->provinsi,
                'kabupaten_kota'     => $user->kabupaten_kota,
                'kecamatan'          => $user->kecamatan,
                'kelurahan'          => $user->kelurahan,
                'status_kepegawaian' => $user->status_kepegawaian,
            ]
        );

        return redirect()->route('participant.training.show', $id)
            ->with('success_enroll', 'Pendaftaran Berhasil! Data profil Anda telah disinkronkan ke pelatihan ini.');
    }

    public function showTrainingDetail($id)
    {
        $training = Training::with(['stages', 'schedules'])->findOrFail($id);
        $user = Auth::user();
        
        // Ambil record participant milik user ini di pelatihan ini
        $participant = Participant::where('training_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Ambil semua form L1 yang telah dibuat Admin
        $formsL1 = \App\Models\EvaluationFormL1::where('training_id', $id)->get();

        return view('participant.training_detail', compact('training', 'participant', 'formsL1', 'user'));
    }

    public function myHistory()
    {
        $user = Auth::user();
        // Mengambil semua data dari tabel participant yang NIP-nya sama dengan user login
        $history = Participant::with('training')
            ->where('nip_nik', $user->nip_nik)
            ->get();

        return view('participant.history', compact('history'));
    }

    // Upload Kelengkapan & Masuk Folder Otomatis
    public function uploadRequirement(Request $request, $id)
    {
        $allowedMimes = ($request->type == 'pas_foto') ? 'jpeg,png,jpg' : 'pdf';
        
        $request->validate([
            'file' => "required|mimes:$allowedMimes|max:5120",
            'type' => 'required|in:biodata,surat_tugas,pas_foto'
        ]);
           
        $user = Auth::user();
        $participant = Participant::where('training_id', $id)->where('user_id', $user->id)->firstOrFail();
        $training = Training::with('folder')->findOrFail($id);

        // PERBAIKAN: Cari peserta berdasarkan training_id DAN (user_id ATAU nip_nik)
        $participant = Participant::where('training_id', $id)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('nip_nik', $user->nip_nik);
            })->first();

        // Jika tidak ditemukan juga, baru kita beri pesan error yang jelas (bukan 404)
        if (!$participant) {
            return redirect()->back()->with('error', 'Data peserta tidak ditemukan. Pastikan Anda sudah terdaftar di pelatihan ini.');
        }

        // Pastikan user_id terhubung jika sebelumnya kosong
        if (empty($participant->user_id)) {
            $participant->update(['user_id' => $user->id]);
        }

         // 1. Validasi File
        $request->validate([
            'file' => 'required|file|max:5120', // Maksimal 5MB
            'type' => 'required|string',
        ], [
            'file.required' => 'Silakan pilih file yang ingin diunggah.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $training = Training::findOrFail($id);
        $user = Auth::user();

        // 2. Dapatkan atau Buat Otomatis Folder Utama Pelatihan jika belum ada
        $mainFolder = Folder::firstOrCreate([
            'training_id' => $id,
            'parent_id'   => null,
        ], [
            'name'        => $training->nama_pelatihan . ' - Angkatan ' . ($training->angkatan ?? '1'),
            'bidang'      => $training->bidang,
            'user_id'     => Auth::id() ?? 1,
            'is_public'   => false,
        ]);

        // 3. Dapatkan/Buat Sub-Folder "KELENGKAPAN PESERTA"
        $subFolder = Folder::firstOrCreate([
            'name'        => 'KELENGKAPAN PESERTA',
            'parent_id'   => $mainFolder->id,
            'training_id' => $id,
            'bidang'      => $training->bidang
        ], [
            'user_id'     => Auth::id()
        ]);

        // 4. Simpan File Fisik ke Storage
        $extension = $request->file('file')->getClientOriginalExtension();
        $cleanUserName = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\-]/', '', $user->name));
        $fileName = strtoupper($request->type) . '_' . $cleanUserName . '_' . time() . '.' . $extension;
        $path = $request->file('file')->storeAs('documents', $fileName, 'public');

        // 5. Catat ke tabel Files di Database
        File::create([
            'folder_id'    => $subFolder->id,
            'display_name' => $fileName,
            'file_path'    => $path,
            'file_type'    => $extension,
            'file_size'    => $request->file('file')->getSize(),
            'user_id'      => Auth::id()
        ]);

        // 6. INI YANG WAJIB ADA: Kembalikan tampilan ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Dokumen ' . strtoupper($request->type) . ' berhasil diunggah!');
    }
}