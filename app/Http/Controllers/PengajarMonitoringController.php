<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Schedule;
use App\Exports\PengajarFinanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajarMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $bulan  = $request->query('bulan');
        $tahun  = $request->query('tahun');

        // Ambil semua user dengan role pengajar
        $pengajars = User::where('role', 'pengajar')
            ->with('pengajar')
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('nip_nik', 'LIKE', "%{$search}%")
                          ->orWhere('jabatan', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(function($user) use ($bulan, $tahun) {
                // Filter JP dan Sesi berdasarkan Periode Bulan & Tahun jika dipilih
                $scheduleQuery = Schedule::where('pengajar_id', $user->id)
                    ->when($bulan, function($q) use ($bulan) {
                        $q->whereMonth('date', $bulan);
                    })
                    ->when($tahun, function($q) use ($tahun) {
                        $q->whereYear('date', $tahun);
                    });

                $user->total_jp = (int) $scheduleQuery->sum('jp');
                $user->total_sessions = $scheduleQuery->count();
                return $user;
            });

        // Statistik Utama
        $stats = [
            'total_pengajar'     => $pengajars->count(),
            'total_jp_global'    => $pengajars->sum('total_jp'),
            'total_sesi_global'  => $pengajars->sum('total_sessions'),
            'rekening_lengkap'   => $pengajars->filter(function($u) {
                return $u->pengajar && !empty($u->pengajar->nomor_rekening);
            })->count(),
        ];

        return view('monitoring.pengajar', compact('pengajars', 'stats', 'search', 'bulan', 'tahun'));
    }

    public function exportFinance(Request $request)
    {
        $search = $request->query('search');
        $bulan  = $request->query('bulan');
        $tahun  = $request->query('tahun');

        $fileName = 'LAPORAN_KEUANGAN_PENGAJAR_' . ($tahun ? $tahun : date('Y')) . '_' . ($bulan ? 'BULAN_' . $bulan : 'ALL') . '.xlsx';

        return Excel::download(new PengajarFinanceExport($search, $bulan, $tahun), $fileName);
    }

    /**
     * Mengunduh PDF Surat Keterangan / Detail JP Berdasarkan Periode
     */
    public function downloadKeteranganMengajarPdf($user_id, Request $request)
    {
        $user  = User::with('pengajar')->findOrFail($user_id);
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Filter jadwal sesi berdasarkan periode yang dipilih
        $schedules = Schedule::where('pengajar_id', $user->id)
            ->with('training')
            ->when($bulan, function($q) use ($bulan) {
                $q->whereMonth('date', $bulan);
            })
            ->when($tahun, function($q) use ($tahun) {
                $q->whereYear('date', $tahun);
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $totalJp = $schedules->sum('jp');
        $totalPelatihan = $schedules->pluck('training_id')->unique()->count();

        // Label Periode untuk ditampilkan di Kop PDF
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($bulan && $tahun) {
            $periodeLabel = ($namaBulan[(int)$bulan] ?? '') . ' ' . $tahun;
        } elseif ($tahun) {
            $periodeLabel = 'Tahun ' . $tahun;
        } elseif ($bulan) {
            $periodeLabel = 'Bulan ' . ($namaBulan[(int)$bulan] ?? '');
        } else {
            $periodeLabel = 'Semua Periode (Keseluruhan)';
        }

        $pdf = Pdf::loadView('monitoring.pdf_keterangan_mengajar', compact(
            'user', 
            'schedules', 
            'totalJp', 
            'totalPelatihan',
            'periodeLabel'
        ));
        $pdf->setPaper('a4', 'portrait');

        $cleanName = strtoupper(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\-]/', '', $user->name)));
        $cleanPeriode = strtoupper(str_replace(' ', '_', $periodeLabel));
        $fileName = 'SURAT_KETERANGAN_MENGAJAR_' . $cleanName . '_' . $cleanPeriode . '.pdf';

        return $pdf->stream($fileName);
    }

    public function viewFile($user_id, $type)
    {
        $user = User::findOrFail($user_id);
        $pengajar = $user->pengajar;

        if (!$pengajar) {
            abort(404, 'Data profil pengajar tidak ditemukan.');
        }

        $filePath = match($type) {
            'cv'          => $pengajar->cv_path,
            'sertifikat'  => $pengajar->sertifikat_path,
            'surat_tugas' => $pengajar->surat_tugas_path,
            default       => null,
        };

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Berkas fisik PDF tidak ditemukan di server.');
        }

        return Storage::disk('public')->response($filePath);
    }
}