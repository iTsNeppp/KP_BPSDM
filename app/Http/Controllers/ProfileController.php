<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil
     */
    public function edit()
    {
        $user = Auth::user();
        if ($user->role === 'pengajar') {
            $user->load('pengajar');
        }
        return view('profile.edit', compact('user'));
    }

    /**
     * Memproses update data profil (Umum & Pengajar beserta upload berkas)
     */
    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        // Aturan validasi dasar
        $rules = [
            'name'          => 'required|string|max:255',
            'whatsapp'      => 'required|numeric',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Tambahan validasi jika user adalah Pengajar dan sedang mengubah data pengajar
        if ($user->role === 'pengajar' && $request->has('nip_nik')) {
            $rules = array_merge($rules, [
                'nip_nik'            => 'required|string|max:50',
                'jabatan'            => 'required|string|max:255',
                'instansi'           => 'required|string|max:255',
                'pangkat_golongan'   => 'required|string|max:100',
                'npwp'               => 'nullable|string|max:50',
                'nomor_rekening'     => 'required|numeric',
                'nama_bank'          => 'required|string|max:100',
                'nama_rekening'      => 'required|string|max:255',
                'file_cv'            => 'nullable|file|mimes:pdf|max:5120',
                'file_sertifikat'    => 'nullable|file|mimes:pdf|max:5120',
                'file_surat_tugas'   => 'nullable|file|mimes:pdf|max:5120',
            ]);
        }

        $request->validate($rules, [
            'file_cv.mimes'          => 'Berkas CV harus berformat PDF.',
            'file_cv.max'            => 'Ukuran berkas CV maksimal 5MB.',
            'file_sertifikat.mimes'  => 'Berkas Sertifikat harus berformat PDF.',
            'file_sertifikat.max'    => 'Ukuran berkas Sertifikat maksimal 5MB.',
            'file_surat_tugas.mimes' => 'Berkas Surat Tugas harus berformat PDF.',
            'file_surat_tugas.max'   => 'Ukuran berkas Surat Tugas maksimal 5MB.',
        ]);

        // 1. Update Foto Profil
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $request->file('profile_photo')->store('avatars', 'public');
        }

        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;

        // 2. Update Data Khusus Pengajar & Berkas Dokumen
        if ($user->role === 'pengajar' && $request->has('nip_nik')) {
            $user->nip_nik = $request->nip_nik;
            $user->jabatan = $request->jabatan;
            $user->instansi = $request->instansi;

            $pengajar = $user->pengajar ?? new Pengajar(['user_id' => $user->id]);

            // Upload CV jika ada berkas baru
            if ($request->hasFile('file_cv')) {
                if ($pengajar->cv_path) {
                    Storage::disk('public')->delete($pengajar->cv_path);
                }
                $pengajar->cv_path = $request->file('file_cv')->store('pengajar/cv', 'public');
            }

            // Upload Sertifikat jika ada berkas baru
            if ($request->hasFile('file_sertifikat')) {
                if ($pengajar->sertifikat_path) {
                    Storage::disk('public')->delete($pengajar->sertifikat_path);
                }
                $pengajar->sertifikat_path = $request->file('file_sertifikat')->store('pengajar/sertifikat', 'public');
            }

            // Upload Surat Tugas jika ada berkas baru
            if ($request->hasFile('file_surat_tugas')) {
                if ($pengajar->surat_tugas_path) {
                    Storage::disk('public')->delete($pengajar->surat_tugas_path);
                }
                $pengajar->surat_tugas_path = $request->file('file_surat_tugas')->store('pengajar/surat_tugas', 'public');
            }

            $pengajar->pangkat_golongan   = $request->pangkat_golongan;
            $pengajar->instansi           = $request->instansi;
            $pengajar->npwp               = $request->npwp;
            $pengajar->nama_bank          = $request->nama_bank;
            $pengajar->nomor_rekening     = $request->nomor_rekening;
            $pengajar->nama_rekening      = $request->nama_rekening;
            $pengajar->save();
        }

        $user->save();

        $message = ($user->role === 'pengajar' && $request->has('nip_nik'))
            ? 'Profil, data rekening, dan berkas pengajar berhasil diperbarui.'
            : 'Profil berhasil diperbarui.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Memproses penggantian password akun
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = User::findOrFail(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
     public function viewPengajarDoc($type)
    {
        $user = Auth::user();
        $pengajar = $user->pengajar;

        if (!$pengajar) {
            abort(404, 'Data pengajar tidak ditemukan.');
        }

        // Tentukan path berdasarkan tipe dokumen yang diminta
        $filePath = match($type) {
            'cv'          => $pengajar->cv_path,
            'sertifikat'  => $pengajar->sertifikat_path,
            'surat_tugas' => $pengajar->surat_tugas_path,
            default       => null,
        };

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Berkas fisik PDF tidak ditemukan di server.');
        }

        // Tampilkan langsung di browser dengan header PDF yang benar
        return Storage::disk('public')->response($filePath);
    }
}