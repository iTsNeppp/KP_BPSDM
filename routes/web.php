<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EvaluationLevel1Controller;
use App\Http\Controllers\EvaluationLevel2Controller;
use App\Http\Controllers\EvaluationLevel34Controller;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\MonitoringIndicatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FollowUpController; 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostEvalControlController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PengajarSetupController; // <-- TAMBAHAN CONTROLLER PENGAJAR

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Akses Tanpa Login - Untuk Peserta, Narasumber, Atasan)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/

// LANDING PAGE (Satu-satunya rute untuk '/')
Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');

// Search Global (Hanya hasil, aksi di dalam auth)
Route::get('/search', [SearchController::class, 'index'])->name('global.search');

// Google Auth
Route::get('auth/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Document Share
Route::get('share/folder/{token}', [DocumentController::class, 'share'])->name('documents.public');
// Tambahan Document Action (Download/View File)
Route::get('/documents/file/{id}/view', [DocumentController::class, 'viewFile'])->name('documents.file.view');
Route::get('/documents/file/{id}/download', [DocumentController::class, 'downloadFile'])->name('documents.file.download');

// Absensi Publik
Route::get('absen/{schedule_id}', [AttendanceController::class, 'publicShow'])->name('public.attendance.show');
Route::get('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicShowDaily'])->name('public.attendance.daily');
Route::post('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicStoreDaily'])->name('public.attendance.store_daily');

// Evaluasi Level 1 Publik
Route::get('evaluasi-l1/form/{training_id}', [EvaluationLevel1Controller::class, 'publicForm'])->name('public.evall1.form');
Route::post('evaluasi-l1/store/{training_id}', [EvaluationLevel1Controller::class, 'publicStore'])->name('public.evall1.store');

// Evaluasi Level 3 & 4 Publik
Route::get('evaluasi-dampak/gateway/{training_id}', [EvaluationLevel34Controller::class, 'publicGateway'])->name('public.l34.gateway');
Route::get('evaluasi-dampak/form/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicForm'])->name('public.l34.form');
Route::post('evaluasi-dampak/store/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicStore'])->name('public.l34.store');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATION SYSTEM
|--------------------------------------------------------------------------
*/
Auth::routes(['register' => false]);

Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah berhasil keluar.');
});

Route::middleware(['auth'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
     
    // --- 02. KELOLA USER (Khusus Superadmin) ---
    Route::middleware(['can:superadmin-only'])->group(function () {
        Route::resource('users', UserController::class);
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // --- PENGATURAN PROFIL UMUM ---
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // =========================================================================
    // --- SETUP PROFIL PENGAJAR (Wajib saat pertama kali Pengajar login) ---
    // =========================================================================
    Route::get('/pengajar/setup-profil', [PengajarSetupController::class, 'index'])->name('pengajar.setup');
    Route::post('/pengajar/setup-profil', [PengajarSetupController::class, 'store'])->name('pengajar.setup.store');

    // --- SETUP PROFIL PESERTA ---
    Route::get('/complete-profile', [ParticipantController::class, 'completeProfile'])->name('participant.profile.complete');
    Route::post('/complete-profile', [ParticipantController::class, 'storeProfile'])->name('participant.profile.store');

    // Rute Menu Jadwal Mengajar (Hanya untuk Pengajar)
    Route::get('/pengajar/jadwal-mengajar', [\App\Http\Controllers\TrainingController::class, 'pengajarSchedules'])->name('pengajar.schedule');

    // --- 03 & 04. KELOLA PELATIHAN ---
    Route::resource('trainings', TrainingController::class);

    // Kelola Dokumen & Folder
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents/folder', [DocumentController::class, 'createFolder'])->name('documents.folder.create');
    Route::post('documents/upload', [DocumentController::class, 'uploadFiles'])->name('documents.upload');
    Route::put('documents/folder/{id}/privacy', [DocumentController::class, 'togglePrivacy'])->name('documents.folder.privacy');
    Route::delete('documents/file/{id}', [DocumentController::class, 'destroyFile'])->name('documents.file.destroy');
    Route::delete('documents/folder/{id}', [DocumentController::class, 'destroyFolder'])->name('documents.folder.destroy');

    // Kelola Pertanyaan
    Route::get('questions/download-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::resource('questions', QuestionController::class);
    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    
    // Sub-Modul: Import Peserta & Kelola Jadwal
    Route::get('trainings/{id}/export-participants-data', [TrainingController::class, 'exportParticipants'])->name('participants.export_data');
    Route::get('trainings/{id}/export-invitation-l34', [EvaluationLevel34Controller::class, 'exportInvitation'])->name('evall34.export_invitation');
    Route::get('trainings/{id}/export-l34', [EvaluationLevel34Controller::class, 'exportExcel'])->name('evall34.export');
    Route::get('trainings/{id}/export-evaluation', [TrainingController::class, 'exportEvaluation'])->name('trainings.export_evaluation');
    Route::get('trainings/{id}/participants', [TrainingController::class, 'showParticipants'])->name('trainings.participants');
    Route::get('trainings/{id}/manage', [TrainingController::class, 'manage'])->name('trainings.manage');
    Route::post('trainings/{id}/participants/import', [TrainingController::class, 'importParticipants'])->name('participants.import');
    Route::put('participants/{id}', [TrainingController::class, 'updateParticipant'])->name('participants.update');
    Route::delete('participants/{id}', [TrainingController::class, 'destroyParticipant'])->name('participants.destroy');
    Route::post('trainings/{id}/participants/manual', [TrainingController::class, 'storeParticipant'])->name('participants.store');
    
    Route::get('trainings/{id}/schedules', [TrainingController::class, 'showSchedules'])->name('trainings.schedules');
    Route::post('trainings/{id}/schedules', [TrainingController::class, 'storeSchedule'])->name('schedules.store');
    Route::put('trainings/{id}/set-lms', [TrainingController::class, 'setLmsLink'])->name('trainings.set_lms');
    Route::put('schedules/{id}', [TrainingController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('schedules/{id}', [TrainingController::class, 'destroySchedule'])->name('schedules.destroy');
    
    Route::get('trainings/{id}/new-code', [TrainingController::class, 'generateNewCode'])->name('trainings.new_code');
    
    // Peserta Join Pelatihan (Tampaknya ada duplikat di bawah, tapi dibiarkan sesuai request)
    Route::post('participant/training/{id}/join', [ParticipantController::class, 'enroll'])->name('participant.training.join');
    Route::get('participant/training/{id}', [ParticipantController::class, 'showTrainingDetail'])->name('participant.training.show');
    Route::post('participant/training/{id}/upload', [ParticipantController::class, 'uploadRequirement'])->name('participant.training.upload');  
    
    Route::get('trainings/{id}/export-word-l34', [EvaluationLevel34Controller::class, 'exportWord'])->name('evall34.export_word');
    Route::get('trainings/{id}/schedules/pdf', [TrainingController::class, 'downloadSchedulePdf'])->name('schedules.pdf');
    Route::get('trainings/{id}/evaluasi-l1/progres', [EvaluationLevel1Controller::class, 'showProgres'])->name('evall1.progres');
    Route::delete('trainings/{id}/evaluasi-l1/destroy', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroy');
    Route::post('trainings/{id}/evaluasi-l1/create-form', [EvaluationLevel1Controller::class, 'storeForm'])->name('evall1.storeForm');
    Route::delete('evaluasi-l1/form/{id}', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroyForm');
    Route::get('trainings/{id}/evaluasi-l2/download-template', [EvaluationLevel2Controller::class, 'downloadTemplate'])->name('evall2.template');
    
    Route::get('evaluasi-l1/export/{form_id}', [EvaluationLevel1Controller::class, 'exportExcel'])->name('evall1.export');
    
    // Rute Admin Kehadiran Harian
    Route::get('attendance/detail/{id}/{date}', [AttendanceController::class, 'showDetailDaily'])->name('attendance.detail.daily');
    Route::get('attendance/pdf-harian/{id}/{date}', [AttendanceController::class, 'downloadPdfDaily'])->name('attendance.pdf.daily');
    
    // --- 07. KEHADIRAN (Monitoring Admin) ---
    Route::get('attendance/excel-all/{id}', [AttendanceController::class, 'downloadExcelAll'])->name('attendance.excel.all');
    Route::get('attendance/pdf-all/{id}', [AttendanceController::class, 'downloadPdfAll'])->name('attendance.pdf.all');
    Route::get('attendance', [AttendanceController::class, 'indexAll'])->name('attendance.all');
    Route::get('trainings/{id}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/detail/{schedule_id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::get('attendance/pdf/{schedule_id}', [AttendanceController::class, 'downloadPdf'])->name('attendance.pdf');
    Route::put('attendance/set-time/{schedule_id}', [AttendanceController::class, 'setTime'])->name('attendance.set-time');
    Route::put('attendance/set-time-date/{training_id}', [AttendanceController::class, 'setTimeByDate'])->name('attendance.set-time-date');
    Route::get('monitoring-indicators', [MonitoringIndicatorController::class, 'index'])->name('indicators.index');
    Route::post('monitoring-indicators', [MonitoringIndicatorController::class, 'store'])->name('indicators.store');
    Route::put('monitoring-indicators/{id}', [MonitoringIndicatorController::class, 'update'])->name('indicators.update');
    Route::delete('monitoring-indicators/{id}', [MonitoringIndicatorController::class, 'destroy'])->name('indicators.destroy');
    Route::post('monitoring-indicators/import', [MonitoringIndicatorController::class, 'import'])->name('indicators.import');
    Route::get('monitoring-indicators/template', [MonitoringIndicatorController::class, 'downloadTemplate'])->name('indicators.template');
    Route::post('monitoring/{id}/store-final', [MonitoringController::class, 'storeFinalSummary'])->name('monitoring.store_final');
    
    // --- 10. MONITORING PENYELENGGARAAN ---
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/{id}/fill', [MonitoringController::class, 'create'])->name('monitoring.fill');
    Route::post('monitoring/{id}/store', [MonitoringController::class, 'store'])->name('monitoring.store');
    
    // Export Monitoring
    Route::get('monitoring/{id}/export-laporan', [MonitoringController::class, 'exportLaporan'])->name('monitoring.export.laporan');
    Route::get('monitoring/{id}/export-tindak-lanjut', [MonitoringController::class, 'exportTindakLanjut'])->name('monitoring.export.tindaklanjut');
    Route::get('monitoring/{id}/export-rekap', [MonitoringController::class, 'exportCeklis'])->name('monitoring.export.rekap');
    Route::get('participants/download-template', [TrainingController::class, 'downloadTemplate'])->name('participants.template');
    Route::get('follow-up', [FollowUpController::class, 'index'])->name('followup.index');
    Route::put('follow-up/{id}/resolve', [FollowUpController::class, 'resolve'])->name('followup.resolve');

    // Export & Import Jadwal Pelatihan
    Route::get('schedules/download-template', [\App\Http\Controllers\TrainingController::class, 'downloadScheduleTemplate'])->name('schedules.template');
    Route::post('trainings/{id}/schedules/import', [\App\Http\Controllers\TrainingController::class, 'importSchedules'])->name('schedules.import');

    // RUTE RIWAYAT PELATIHAN PENGAJAR
    Route::get('/pengajar/riwayat-pelatihan', [\App\Http\Controllers\TrainingController::class, 'pengajarHistory'])->name('pengajar.history');

    // --- MONITORING PENGAJAR & KEUANGAN (ADMIN & SUPERADMIN) ---
    Route::get('/monitoring-pengajar', [\App\Http\Controllers\PengajarMonitoringController::class, 'index'])->name('monitoring.pengajar.index');
    Route::get('/monitoring-pengajar/export-keuangan', [\App\Http\Controllers\PengajarMonitoringController::class, 'exportFinance'])->name('monitoring.pengajar.export');

    Route::post('/pengajar/training/{id}/upload-surat-tugas', [\App\Http\Controllers\TrainingController::class, 'uploadSuratTugasPengajar'])->name('pengajar.surat_tugas.upload');

    
    // RUTE KHUSUS MELIHAT BERKAS PENGAJAR (CV, SERTIFIKAT, SURAT TUGAS)
    Route::get('/pengajar/dokumen/view/{type}', [\App\Http\Controllers\ProfileController::class, 'viewPengajarDoc'])->name('pengajar.doc.view');

    // RUTE STREAMING MEMBUKA SURAT TUGAS PENGAJAR
    Route::get('/pengajar/training/{id}/surat-tugas/view', [\App\Http\Controllers\TrainingController::class, 'viewSuratTugasPengajar'])->name('pengajar.surat_tugas.view');

    // RUTE STREAMING BERKAS PENGAJAR UNTUK ADMIN MONITORING
    Route::get('/monitoring-pengajar/{user_id}/file/{type}', [\App\Http\Controllers\PengajarMonitoringController::class, 'viewFile'])->name('monitoring.pengajar.file.view');

     // RUTE UNDUH PDF KETERANGAN / DETAIL MENGAJAR PENGAJAR
    Route::get('/monitoring-pengajar/{user_id}/pdf-keterangan', [\App\Http\Controllers\PengajarMonitoringController::class, 'downloadKeteranganMengajarPdf'])->name('monitoring.pengajar.keterangan.pdf');

    // --- EVALUASI KIRKPATRICK (Admin View) ---

    // Level 1: Reaction
    Route::get('control-l34', [PostEvalControlController::class, 'index'])->name('control_l34.index');
    Route::get('evaluasi/l1', [EvaluationLevel1Controller::class, 'indexAll'])->name('evaluasi.l1'); // List Pelatihan L1
    Route::get('trainings/{id}/evaluasi-l1', [EvaluationLevel1Controller::class, 'index'])->name('evall1.index'); // Detail L1
    Route::get('evaluasi-l2/download-template', [EvaluationLevel2Controller::class, 'downloadTemplate'])->name('evall2.template');
    Route::get('evaluasi-l34/download-template', [EvaluationLevel34Controller::class, 'downloadTemplate'])->name('evall34.template');
    
    // Level 2: Learning
    Route::get('evaluasi/l2', [EvaluationLevel2Controller::class, 'indexAll'])->name('evaluasi.l2'); // List Pelatihan L2
    Route::get('trainings/{id}/evaluasi-l2', [EvaluationLevel2Controller::class, 'index'])->name('evall2.index'); // Detail L2
    Route::post('evaluasi-l2/update-single', [EvaluationLevel2Controller::class, 'updateSingle'])->name('evall2.update-single');
    Route::post('trainings/{id}/evaluasi-l2/import', [EvaluationLevel2Controller::class, 'importExcel'])->name('evall2.import');

    // Level 3 & 4: Impact (360)
    Route::get('evaluasi/l34', [EvaluationLevel34Controller::class, 'indexAll'])->name('evaluasi.l34'); // List Pelatihan L34
    Route::get('trainings/{id}/evaluasi-l34', [EvaluationLevel34Controller::class, 'index'])->name('evall34.index'); // Detail L34

    // Kelola Alumni
    Route::get('alumni-statistics/export', [AlumniController::class, 'exportExcel'])->name('alumni.export');
    Route::get('alumni', [AlumniController::class, 'index'])->name('alumni.index');
        
    // --- 3. RUTE KHUSUS PESERTA (Sudah Login & Role Participant) ---
    Route::middleware(['can:isParticipant'])->prefix('participant')->group(function () {
        Route::get('/dashboard', [ParticipantController::class, 'index'])->name('participant.dashboard');
        Route::get('/trainings', [ParticipantController::class, 'availableTrainings'])->name('participant.trainings');
    
        // Proses Daftar (Join)
        Route::post('/training/{id}/join-enroll', [ParticipantController::class, 'enroll'])->name('participant.training.join_enroll');
        Route::post('/training/{id}/upload-req', [ParticipantController::class, 'uploadRequirement'])->name('participant.training.upload_req');
        
        // Detail Pelatihan
        Route::get('/training/{id}/show', [ParticipantController::class, 'showTrainingDetail'])->name('participant.training.detail');
        
        // Riwayat
        Route::get('/history', [ParticipantController::class, 'myHistory'])->name('participant.history');
    });

});