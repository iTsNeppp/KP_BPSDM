<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use App\Models\MonitoringResult;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ==========================================
        // 1. JIKA ROLE ADALAH PARTICIPANT (PESERTA)
        // ==========================================
        if ($user->role === 'participant') {
            return redirect()->route('participant.dashboard');
        }

        // ==========================================
        // 2. JIKA ROLE ADALAH PENGAJAR
        // ==========================================
        if ($user->role === 'pengajar') {
            // Jika data pengajar belum ada atau rekening masih kosong, paksa ke setup
            if (!$user->pengajar || empty($user->pengajar->nomor_rekening) || empty($user->pengajar->nama_bank)) {
                return redirect()->route('pengajar.setup')
                    ->with('warning', 'Silakan lengkapi profil dan data rekening Anda terlebih dahulu.');
            }

            $user->load('pengajar');

            // 1. Data Pelatihan yang diajar oleh pengajar ini
            $myTrainings = Training::whereHas('schedules', function($q) use ($user) {
                $q->where('pengajar_id', $user->id);
            })->get();

            // Pelatihan pada tahun berjalan
            $myTrainingsThisYear = Training::whereHas('schedules', function($q) use ($user) {
                $q->where('pengajar_id', $user->id);
            })->whereYear('tgl_mulai', date('Y'))->get();

            // 2. TOTAL JP (Diambil langsung dari tabel SCHEDULES milik pengajar ini)
            $totalJp = abs((int) Schedule::where('pengajar_id', $user->id)->sum('jp'));

            // 3. JP TAHUN INI (Diambil dari kolom JP tabel SCHEDULES berdasarkan tahun pada tanggal jadwal 'date')
            $jpTahunIni = abs((int) Schedule::where('pengajar_id', $user->id)
                ->whereYear('date', date('Y'))
                ->sum('jp'));

            // 4. Jumlah Pelatihan
            $totalPelatihan = $myTrainings->count();

            // 5. Pelatihan Tahun Ini
            $pelatihanTahunIni = $myTrainingsThisYear->count();
            
            // 6. Persentase Capaian JP (Target 20 JP per tahun, dikunci maksimal 100% dan minimal 0%)
            $targetJp = 20; 
            $persentaseJp = $targetJp > 0 ? min(100, max(0, round(($jpTahunIni / $targetJp) * 100))) : 0;

            return view('pengajar.dashboard', compact(
                'user', 
                'totalJp', 
                'jpTahunIni', 
                'totalPelatihan', 
                'pelatihanTahunIni', 
                'persentaseJp'
            ));
        }

        // ==========================================
        // 3. JIKA ROLE ADALAH SUPERADMIN / ADMIN BIDANG
        // ==========================================
        $query = Training::query();

        // Role Filter (Admin Bidang hanya melihat bidangnya sendiri)
        if ($user->role !== 'superadmin') {
            $query->where('bidang', $user->bidang);
        }

        $trainingIds = $query->pluck('id');

        // 1. STATISTIK UTAMA
        $stats = [
            'total_training'        => $query->count(),
            'total_participants'    => Participant::whereIn('training_id', $trainingIds)->count(),
            'avg_attendance'        => Attendance::whereHas('schedule', function($q) use ($trainingIds){
                                            $q->whereIn('training_id', $trainingIds);
                                        })->where('status', 'hadir')->count(),
            'total_attendance_logs' => Attendance::whereHas('schedule', function($q) use ($trainingIds){
                                            $q->whereIn('training_id', $trainingIds);
                                        })->count(),
        ];

        // 2. KIRKPATRICK LEVEL 1 (Reaction)
        $avgL1 = EvaluationResultL1::whereIn('training_id', $trainingIds)->avg('score') ?? 0;

        // 3. KIRKPATRICK LEVEL 2 (Learning - Pre vs Post)
        $avgL2Pre = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('pretest') ?? 0;
        $avgL2Post = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('postest') ?? 0;

        // 4. KIRKPATRICK LEVEL 3 & 4 (Impact 360)
        $avgL3 = EvaluationResultL34::whereIn('training_id', $trainingIds)->where('evaluator_role', '!=', 'mandiri')->avg('score') ?? 0;
        $avgL4 = EvaluationResultL34::whereIn('training_id', $trainingIds)->avg('score') ?? 0; // Agregat seluruhnya

        // 5. MONITORING SUMMARY (Compliance)
        $monYa    = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'ya')->count();
        $monTidak = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'tidak')->count();

        // 6. PELATIHAN TERBARU
        $latestTrainings = Training::with('schedules')->whereIn('id', $trainingIds)->latest()->take(5)->get();

        // Tampilkan Dashboard Admin
        return view('dashboard.index', compact(
            'stats', 'avgL1', 'avgL2Pre', 'avgL2Post', 'avgL3', 'avgL4', 'monYa', 'monTidak', 'latestTrainings'
        ));
    }
}