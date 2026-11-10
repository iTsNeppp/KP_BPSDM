<?php

namespace App\Http\Controllers;

use App\Models\Training;  
use App\Models\Schedule;    
use App\Models\Participant; 
use App\Models\User; 
use App\Models\Folder; 
use App\Models\EvaluationFormL1;
use App\Models\Pengajar;
use App\Helpers\LogHelper;
use App\Imports\ParticipantImport;
use App\Exports\TrainingEvaluationExport;
use App\Exports\ParticipantTemplateExport;
use App\Exports\ParticipantExport;
use App\Exports\ScheduleTemplateExport;
use App\Imports\ScheduleImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; 

class TrainingController extends Controller
{
    public function index()
    {
        $query = Training::with('schedules');
        
        // Jika bukan superadmin, filter berdasarkan bidang user
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();
        return view('trainings.index', compact('trainings'));
    }

    public function create(Request $request)
    {
        $model = $request->query('model', 'standar'); // standar atau blended
        return view('trainings.create', compact('model'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'nama_pelatihan' => 'required',
            'bidang'         => 'required',
            'model'          => 'required',
            'lokasi'         => 'required',
            'angkatan'       => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp'             => 'required|numeric',
            'tgl_mulai'      => 'required|date',
            'tgl_selesai'    => 'required|date',
        ];
        if ($request->model === 'standar') { $rules['metode'] = 'required'; }
        $data = $request->validate($rules);

        if ($request->model === 'blended') { $data['metode'] = 'blended'; }

        // 2. Simpan Data Pelatihan
        $training = Training::create($data);

        // 3. LOGIKA OTOMATIS: BUAT FOLDER DOKUMEN
        Folder::create([
            'training_id' => $training->id,
            'name'        => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan,
            'bidang'      => $training->bidang,
            'user_id'     => Auth::id(),
            'parent_id'   => null,
            'is_public'   => false,
        ]);

        // 4. Simpan Tahapan (Jika Blended)
        if ($request->model === 'blended' && $request->has('stages')) {
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode'       => $stage['metode'],
                    'tgl_mulai'    => $stage['mulai'],
                    'tgl_selesai'  => $stage['selesai'],
                ]);
            }
        }

        LogHelper::record('Pelatihan', 'Membuat pelatihan & folder dokumen: ' . $training->nama_pelatihan);

        return redirect()->route('trainings.index')->with('success', 'Pelatihan dan Folder Dokumen berhasil dibuat.');
    }

    public function storeParticipant(Request $request, $id)
    {
        $request->validate([
            'nip_nik'            => 'required|string|unique:participants,nip_nik,NULL,id,training_id,' . $id,
            'name'               => 'required|string|max:255',
            'gender'             => 'required',
            'phone'              => 'required|string|max:20',
            'jabatan'            => 'required',
            'instansi'           => 'required',
            'provinsi'           => 'required',
            'kabupaten_kota'     => 'required',
            'status_kepegawaian' => 'required',
        ]);

        Participant::create([
            'training_id'        => $id,
            'nip_nik'            => ltrim($request->nip_nik, "'"),
            'name'               => $request->name,
            'gender'             => $request->gender,
            'phone'              => $request->phone,
            'jabatan'            => $request->jabatan,
            'instansi'           => $request->instansi,
            'provinsi'           => $request->provinsi,
            'kabupaten_kota'     => $request->kabupaten_kota,
            'status_kepegawaian' => $request->status_kepegawaian,
        ]);

        return redirect()->back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function showParticipants(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $search = $request->query('search');

        $participants = Participant::where('training_id', $id)
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('nip_nik', 'LIKE', "%$search%")
                    ->orWhere('instansi', 'LIKE', "%$search%");
                });
            })
            ->get();
        
        return view('trainings.participants', compact('training', 'participants', 'search'));
    }

    public function updateParticipant(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $request->validate([
            'nip_nik'            => 'required|string|unique:participants,nip_nik,' . $id . ',id,training_id,' . $participant->training_id,
            'name'               => 'required|string|max:255',
            'gender'             => 'required',
            'phone'              => 'required|string|max:20',
            'jabatan'            => 'required',
            'instansi'           => 'required',
            'provinsi'           => 'required',
            'kabupaten_kota'     => 'required',
            'status_kepegawaian' => 'required',
        ]);

        $participant->update([
            'nip_nik'            => ltrim($request->nip_nik, "'"),
            'name'               => $request->name,
            'gender'             => $request->gender,
            'phone'              => $request->phone,
            'jabatan'            => $request->jabatan,
            'instansi'           => $request->instansi,
            'provinsi'           => $request->provinsi,
            'kabupaten_kota'     => $request->kabupaten_kota,
            'status_kepegawaian' => $request->status_kepegawaian,
        ]);

        return redirect()->back()->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroyParticipant($id)
    {
        Participant::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Peserta berhasil dihapus.');
    }

    /**
     * MENAMPILKAN JADWAL & DAFTAR PENGAJAR
     */
    public function showSchedules($id) 
    {
        $training = Training::findOrFail($id);
        
        // Eager load data relasi pengajar
        $schedules = Schedule::with('pengajar')
            ->where('training_id', $id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Ambil daftar user dengan role pengajar
        $pengajars = User::where('role', 'pengajar')->orderBy('name', 'asc')->get();

        return view('trainings.schedules', compact('training', 'schedules', 'pengajars'));
    }

    public function importParticipants(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new ParticipantImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Data peserta berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new ParticipantTemplateExport(), 
            'template_peserta_integral.xlsx'
        );
    }

    /**
     * MENYIMPAN JADWAL BARU (DENGAN JP, LINK ZOOM & PENGAJAR)
     */
    public function storeSchedule(Request $request, $id)
    {
        $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'activity'    => 'required|string|max:255',
            'jp'          => 'nullable|numeric|min:1',
            'link_zoom'   => 'nullable|string|max:500',
            'pic'         => 'required|string|max:255',
            'pengajar_id' => 'nullable|exists:users,id',
        ]);

        Schedule::create([
            'training_id' => $id,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'activity'    => $request->activity,
            'jp'          => $request->jp,
            'link_zoom'   => $request->link_zoom,
            'pic'         => $request->pic,
            'pengajar_id' => $request->pengajar_id,
        ]);

        return redirect()->back()->with('success', 'Sesi jadwal berhasil ditambahkan.');
    }

    /**
     * MEMPERBARUI SESI JADWAL (DENGAN JP, LINK ZOOM & PENGAJAR)
     */
    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'activity'    => 'required|string|max:255',
            'jp'          => 'nullable|numeric|min:1',
            'link_zoom'   => 'nullable|string|max:500',
            'pic'         => 'required|string|max:255',
            'pengajar_id' => 'nullable|exists:users,id',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update([
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'activity'    => $request->activity,
            'jp'          => $request->jp,
            'link_zoom'   => $request->link_zoom,
            'pic'         => $request->pic,
            'pengajar_id' => $request->pengajar_id,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroySchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Sesi jadwal berhasil dihapus.');
    }

    public function downloadSchedulePdf($id)
    {
        $user = Auth::user();
        $training = Training::findOrFail($id);

        // Query dasar jadwal pelatihan
        $schedulesQuery = $training->schedules()
            ->with('pengajar')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc');

        // PENGECEKAN ROLE PENGGUNA
        $isPengajar = ($user && $user->role === 'pengajar');

        if ($isPengajar) {
            // 1. JIKA PENGAJAR: Hanya ambil jadwal sesi yang ditugaskan ke dirinya sendiri
            $schedules = $schedulesQuery->where('pengajar_id', $user->id)->get();
            $pdfTitle  = "JADWAL MENGAJAR TENAGA PENGAJAR / FASILITATOR";
            
            // Nama file khusus pengajar
            $cleanPengajarName = strtoupper(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\-]/', '', $user->name)));
            $fileName = "JADWAL_MENGAJAR_" . $cleanPengajarName . "_" . str_replace(' ', '_', $training->nama_pelatihan) . ".pdf";
        } else {
            // 2. JIKA ADMIN / PESERTA: Ambil seluruh sesi jadwal lengkap
            $schedules = $schedulesQuery->get();
            $pdfTitle  = "JADWAL KEGIATAN PELATIHAN";
            $fileName  = "JADWAL_PELATIHAN_" . str_replace(' ', '_', $training->nama_pelatihan) . ".pdf";
        }

        // Generate PDF
        $pdf = Pdf::loadView('trainings.pdf_schedule', compact(
            'training', 
            'schedules', 
            'isPengajar', 
            'pdfTitle', 
            'user'
        ));
        $pdf->setPaper('a4', 'landscape');

        $fileContent = $pdf->output();

        // Otomatis arsipkan ke folder dokumen HANYA jika didownload oleh Admin (dokumen master)
        if (!$isPengajar) {
            \App\Http\Controllers\DocumentController::archiveInternal(
                $training->id, 
                'JADWAL PELATIHAN', 
                $fileName, 
                $fileContent, 
                'pdf'
            );
        }

        return response()->streamDownload(function() use($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    public function edit($id)
    {
        $training = Training::with('stages')->findOrFail($id);
        $model = $training->model;

        return view('trainings.edit', compact('training', 'model'));
    }

    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        $rules = [
            'nama_pelatihan' => 'required',
            'bidang'         => 'required',
            'lokasi'         => 'required',
            'angkatan'       => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp'             => 'required|numeric',
            'tgl_mulai'      => 'required|date',
            'tgl_selesai'    => 'required|date',
        ];

        if ($training->model === 'standar') {
            $rules['metode'] = 'required';
        }

        $data = $request->validate($rules);

        $training->update($request->all());
        
        Folder::where('training_id', $training->id)->update([
            'name' => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan
        ]);

        if ($training->model === 'blended' && $request->has('stages')) {
            $training->stages()->delete();
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode'       => $stage['metode'],
                    'tgl_mulai'    => $stage['mulai'],
                    'tgl_selesai'  => $stage['selesai'],
                ]);
            }
        }

        return redirect()->route('trainings.index')->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $training->delete();

        return redirect()->route('trainings.index')->with('success', 'Pelatihan berhasil dihapus dari sistem.');
    }

    public function exportEvaluation($id)
    {
        $training = Training::findOrFail($id);
        $export = new TrainingEvaluationExport($training);
        $fileName = 'HASIL_EVALUASI_L1_L2_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'HASIL EVALUASI L1 L2', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }

    public function generateNewCode($id) 
    {
        $training = Training::findOrFail($id);
        $training->update(['invitation_code' => strtoupper(Str::random(6))]);
        return redirect()->back()->with('success', 'Kode Undangan diperbarui: ' . $training->invitation_code);
    }

    public function setLmsLink(Request $request, $id)
    {
        $request->validate([
            'link_lms' => 'required|url'
        ], [
            'link_lms.url' => 'Format link tidak valid (gunakan http:// atau https://)'
        ]);

        $training = Training::findOrFail($id);
        $training->update(['link_lms' => $request->link_lms]);

        return redirect()->back()->with('success', 'Link LMS berhasil diperbarui.');
    }

    public function manage($id)
    {
        $training = Training::withCount('participants')->with(['schedules'])->findOrFail($id);
        $formsL1 = EvaluationFormL1::where('training_id', $id)->get();

        return view('trainings.manage', compact('training', 'formsL1'));
    }

    public function exportParticipants($id)
    {
        $training = Training::findOrFail($id);
        $export = new ParticipantExport($id);
        $fileName = 'DATA_PESERTA_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        \App\Http\Controllers\DocumentController::archiveInternal($id, 'DATA PESERTA PELATIHAN', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function() use($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    /**
     * Menampilkan daftar pelatihan dan jadwal sesi yang diajar oleh Pengajar yang login
     */
    public function pengajarSchedules()
    {
        $user = Auth::user();

        // Ambil semua pelatihan di mana user ini ditugaskan sebagai pengajar pada sesinya
        $myTrainings = Training::whereHas('schedules', function($q) use ($user) {
            $q->where('pengajar_id', $user->id);
        })->with(['schedules' => function($q) use ($user) {
            $q->where('pengajar_id', $user->id)->orderBy('date', 'asc')->orderBy('start_time', 'asc');
        }])->latest()->get();

        return view('pengajar.schedule', compact('myTrainings', 'user'));
    }

    /**
     * Download Template Excel Jadwal Pelatihan
     */
    public function downloadScheduleTemplate()
    {
        return Excel::download(new ScheduleTemplateExport(), 'template_jadwal_pelatihan.xlsx');
    }

    /**
     * Import Jadwal Pelatihan dari Excel
     */
    public function importSchedules(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120'
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.', 
            'file.mimes'    => 'Format file harus berupa (.xlsx atau .xls).',
            'file.max'      => 'Ukuran file maksimal 5MB.'
        ]);

        Excel::import(new ScheduleImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Jadwal pelatihan berhasil diimport.');
    }
    /**
     * Menampilkan riwayat pelatihan yang telah selesai diajar oleh Pengajar
     */
    public function pengajarHistory(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search');

        // Ambil pelatihan yang SUDAH SELESAI (tgl_selesai < sekarang) dan pernah diajar oleh user ini
        $trainings = Training::whereHas('schedules', function($q) use ($user) {
                $q->where('pengajar_id', $user->id);
            })
            ->where('tgl_selesai', '<', now()) // Hanya pelatihan yang sudah selesai
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('nama_pelatihan', 'LIKE', "%{$search}%")
                          ->orWhere('bidang', 'LIKE', "%{$search}%")
                          ->orWhere('lokasi', 'LIKE', "%{$search}%");
                });
            })
            ->with(['schedules' => function($q) use ($user) {
                $q->where('pengajar_id', $user->id)->orderBy('date', 'asc')->orderBy('start_time', 'asc');
            }])
            ->latest('tgl_selesai')
            ->get();

        // Total akumulasi JP dari seluruh riwayat pelatihan yang selesai
        $totalJpRiwayat = Schedule::where('pengajar_id', $user->id)
            ->whereHas('training', function($q) {
                $q->where('tgl_selesai', '<', now());
            })
            ->sum('jp');

        return view('pengajar.history', compact('trainings', 'user', 'search', 'totalJpRiwayat'));
    }
     public function viewSuratTugasPengajar($id)
    {
        $user = Auth::user();

        // 1. Cari path surat tugas dari jadwal sesi pelatihan ini
        $schedule = Schedule::where('training_id', $id)
            ->where('pengajar_id', $user->id)
            ->whereNotNull('surat_tugas')
            ->first();

        $path = $schedule ? $schedule->surat_tugas : ($user->pengajar->surat_tugas_path ?? null);

        // 2. Cek apakah file fisik ada di storage
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'Berkas fisik PDF Surat Tugas tidak ditemukan di server.');
        }

        // 3. Alirkan file langsung sebagai response PDF ke browser
        return Storage::disk('public')->response($path);
    }
}