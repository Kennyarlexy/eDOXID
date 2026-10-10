<?php

use App\Http\Controllers\Admin\AdminController;
// Admin Controller
use App\Http\Controllers\Admin\BagianAdministrasiController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\FinalStepController as AdminFinalStepController;
use App\Http\Controllers\Admin\FormatController;
use App\Http\Controllers\Admin\GradingController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SkillGroupController;
use App\Http\Controllers\Admin\SkillGroupLecturerController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\SubmissionPresenceController as AdminSubmissionPresenceController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\BagianAdministrasi\FinalStepController as BagianAdministrasiFinalStepController;
// Dosen Controller
use App\Http\Controllers\BagianAdministrasi\SubmissionController as BagianAdministrasiSubmissionController;
use App\Http\Controllers\Dosen\FinalStepController as DosenFinalStepController;
use App\Http\Controllers\Dosen\SubmissionController as DosenSubmissionController;
// Bagian Administrasi Controller
use App\Http\Controllers\Dosen\SubmissionPresenceController as DosenSubmissionPresenceController;
use App\Http\Controllers\IndexController;
// Mahasiswa Controller
use App\Http\Controllers\Mahasiswa\FinalStepController as MahasiswaFinalStepController;
use App\Http\Controllers\Mahasiswa\SubmissionController as MahasiswaSubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\Mahasiswa;
use Illuminate\Support\Facades\Route;

Route::prefix('panel')->name('panel.')->middleware(['auth', 'isActive'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('dashboard');
    Route::get('/profile', [IndexController::class, 'profile'])->name('profile');
    Route::put('/profile', [IndexController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [IndexController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/avatar', [IndexController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::put('/profile/signature', [IndexController::class, 'updateSignature'])->name('profile.signature.update');

    Route::get('/profile/sync-spota', [IndexController::class, 'syncSpotaUserData'])->name('profile.sync-spota');

    Route::get('/pemberitahuan', [NotificationController::class, 'index'])->name('notification.index');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::name('users.')->prefix('/users')->group(function () {
            Route::resource('/admin', AdminController::class)->only('index');
            Route::get('/bagian-administrasi/{id}/log', [BagianAdministrasiController::class, 'log'])->name('bagian-administrasi.log');
            Route::get('/dosen/{id}/log', [DosenController::class, 'log'])->name('dosen.log');
            Route::resource('/bagian-administrasi', BagianAdministrasiController::class);

            Route::resource('/dosen', DosenController::class)->except(['create', 'store', 'destroy', 'show']);
            Route::resource('/mahasiswa', MahasiswaController::class)->except(['create', 'store', 'destroy', 'show']);

            Route::get('/{id}/log', [IndexController::class, 'userLog'])->name('log');
        });

        Route::get('/pengajuan/{type_slug}', [SubmissionController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/{type_slug}/{id}', [SubmissionController::class, 'show'])->name('pengajuan.show');
        Route::put('/pengajuan/{type_slug}/{id}', [SubmissionController::class, 'update'])->name('pengajuan.update');

        Route::get('/pengajuan/{type_slug}/{id}/berkas', [SubmissionController::class, 'showRequiredFiles'])->name('pengajuan.required_files.show');
        Route::put('/pengajuan/{type_slug}/{id}/berkas', [SubmissionController::class, 'updateRequiredFiles'])->name('pengajuan.required_files.update');

        Route::get('/pengajuan/{type_slug}/{id}/verifikators', [SubmissionController::class, 'showVerificators'])->name('pengajuan.verifikators.show');
        Route::put('/pengajuan/{type_slug}/{id}/verifikators', [SubmissionController::class, 'updateVerificators'])->name('pengajuan.verifikators.update');

        Route::get('/pengajuan/{type_slug}/{id}/penilaian', [SubmissionController::class, 'showEvaluation'])->name('pengajuan.evaluation.show');
        Route::put('/pengajuan/{type_slug}/{id}/penilaian', [SubmissionController::class, 'updateEvaluation'])->name('pengajuan.evaluation.update');

        Route::post('/pengajuan/{type_slug}/{id}/updateFile', [SubmissionController::class, 'updateFile'])->name('pengajuan.updateFile');

        Route::get('/pengajuan/{type_slug}/{id}/result', [SubmissionController::class, 'resultSubmission'])->name('pengajuan.result.show');
        Route::put('/pengajuan/{type_slug}/{id}/result', [SubmissionController::class, 'resultSubmissionUpdate'])->name('pengajuan.result.update');

        Route::post('/pengajuan/{type_slug}/{id}/upload/result', [SubmissionController::class, 'uploadFileFinalDocument'])->name('pengajuan.result.upload');

        Route::get('/pengajuan/{type_slug}/{id}/memutuskan-hasil', [SubmissionController::class, 'makeResultSubmission'])->name('pengajuan.make-result.show');
        Route::put('/pengajuan/{type_slug}/{id}/memutuskan-hasil', [SubmissionController::class, 'makeResultSubmissionUpdate'])->name('pengajuan.make-result.update');

        Route::get('/pengajuan/{type_slug}/{submission_id}/daftar-hadir', [AdminSubmissionPresenceController::class, 'index'])->name('pengajuan.presences.index');
        Route::post('/pengajuan/{type_slug}/{submission_id}/daftar-hadir', [AdminSubmissionPresenceController::class, 'store'])->name('pengajuan.presences.store');
        Route::put('/pengajuan/{type_slug}/{submission_id}/daftar-hadir/{id}', [AdminSubmissionPresenceController::class, 'update'])->name('pengajuan.presences.update');
        Route::delete('/pengajuan/{type_slug}/{submission_id}/daftar-hadir/{id}', [AdminSubmissionPresenceController::class, 'destroy'])->name('pengajuan.presences.destroy');

        Route::put('/pengajuan/{type_slug}/{submission_id}/publik-url-daftar-hadir', [AdminSubmissionPresenceController::class, 'updatePublicUrlPresence'])->name('pengajuan.presences.public-url.update');

        Route::get('/pengajuan/{type_slug}/{id}/diskusi', [SubmissionController::class, 'showDiscussions'])->name('pengajuan.discussions.show');

        Route::delete('/pengajuan/{type_slug}/{id}', [SubmissionController::class, 'destroy'])->name('pengajuan.destroy');

        Route::prefix('/kelompok-keahlian')->name('kelompok-keahlian.')->group(function () {
            Route::resource('/{skill_group_id}/daftar-dosen', SkillGroupLecturerController::class)->only(['index']);
        });
        Route::resource('/kelompok-keahlian', SkillGroupController::class)->except(['show']);

        Route::resource('/type', TypeController::class);
        Route::prefix('type')->name('type.')->group(function () {
            Route::put('/change-to-primary/format/{format_id}', [FormatController::class, 'changeToPrimary'])->name('format.change-to-primary');
            Route::resource('/{type}/format', FormatController::class);
        });

        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::put('/setting', [SettingController::class, 'update'])->name('setting.update');

        Route::get('/setting/whatsapp', [SettingController::class, 'whatsappIndex'])->name('setting.whatsapp.index');
        Route::put('/setting/whatsapp', [SettingController::class, 'whatsappUpdate'])->name('setting.whatsapp.update');

        Route::get('/setting/google-drive', [SettingController::class, 'googleDriveIndex'])->name('setting.google.drive.index');
        Route::put('/settng/google-drive', [SettingController::class, 'googleDriveUpdate'])->name('setting.google.drive.update');

        Route::get('/setting/sync-spota', [SettingController::class, 'syncSpota'])->name('setting.sync-spota');

        Route::resource('/grading', GradingController::class)->except(['show']);

        Route::get('/sinkronisasi/pengajuan/format', [SubmissionController::class, 'syncSubmissionFormat'])->name('submission.sync-format');

        Route::get('/tahapan-akhir', [AdminFinalStepController::class, 'index'])->name('tahapan-akhir.index');
        Route::get('/tahapan-akhir/{student_id}', [AdminFinalStepController::class, 'show'])->name('tahapan-akhir.show');
        Route::put('/tahapan-akhir/{student_id}', [AdminFinalStepController::class, 'update'])->name('tahapan-akhir.update');
        Route::post('/tahapan-akhir/upload/{student_id}', [AdminFinalStepController::class, 'upload'])->name('tahapan-akhir.upload');
    });

    Route::prefix('dosen')->name('dosen.')->middleware('dosen')->group(function () {
        // Route::get('/pengajuan-public', [DosenSubmissionController::class, 'publicIndex'])->name('pengajuan-public.index');
        // Route::get('/pengajuan-public/{id}', [DosenSubmissionController::class, 'publicShow'])->name('pengajuan-public.show');
        // Route::post('/pengajuan-public/{id}/add-comment', [DosenSubmissionController::class, 'publicAddComment'])->name('pengajuan-public.add-comment');

        Route::get('/pengajuan/{type_slug}', [DosenSubmissionController::class, 'index'])->name('pengajuan.index');

        Route::get('/pengajuan/{type_slug}/{id}', [DosenSubmissionController::class, 'show'])->name('pengajuan.show');
        Route::put('/pengajuan/{type_slug}/{id}', [DosenSubmissionController::class, 'update'])->name('pengajuan.update');

        Route::put('/pengajuan-bypass/{type_slug}/{id}', [DosenSubmissionController::class, 'updateBypass'])->name('pengajuan.updateBypass');

        Route::get('/pengajuan/{type_slug}/{id}/berkas', [DosenSubmissionController::class, 'showRequiredFiles'])->name('pengajuan.required_files.show');

        Route::get('/pengajuan/{type_slug}/{id}/diskusi', [DosenSubmissionController::class, 'showDiscussion'])->name('pengajuan.discussions.show');
        Route::post('/pengajuan/{type_slug}/{id}/diskusi', [DosenSubmissionController::class, 'addDiscussion'])->name('pengajuan.discussions.add');

        Route::get('/pengajuan/{type_slug}/{id}/verifikators', [DosenSubmissionController::class, 'showVerificators'])->name('pengajuan.verifikators.show');
        Route::put('/pengajuan/{type_slug}/{id}/verifikators', [DosenSubmissionController::class, 'updateVerificators'])->name('pengajuan.verifikators.update');

        Route::get('/pengajuan/{type_slug}/{id}/penilaian', [DosenSubmissionController::class, 'showEvaluation'])->name('pengajuan.evaluation.show');
        Route::put('/pengajuan/{type_slug}/{id}/penilaian', [DosenSubmissionController::class, 'updateEvaluation'])->name('pengajuan.evaluation.update');

        Route::get('/pengajuan/{type_slug}/{id}/result', [DosenSubmissionController::class, 'resultSubmission'])->name('pengajuan.result.show');

        Route::get('/pengajuan/{type_slug}/{id}/memutuskan-hasil', [DosenSubmissionController::class, 'makeResultSubmission'])->name('pengajuan.make-result.show');
        Route::put('/pengajuan/{type_slug}/{id}/memutuskan-hasil', [DosenSubmissionController::class, 'makeResultSubmissionUpdate'])->name('pengajuan.make-result.update');

        Route::get('/pengajaun/{type_slug}/{id}/revisi', [DosenSubmissionController::class, 'revisionSubmission'])->name('pengajuan.revision.show');
        Route::put('/pengajuan/{type_slug}/{id}/revisi', [DosenSubmissionController::class, 'revisionSubmissionUpdate'])->name('pengajuan.revision.update');

        Route::get('/pengajuan/{type_slug}/{submission_id}/daftar-hadir', [DosenSubmissionPresenceController::class, 'index'])->name('pengajuan.presences.index');
        Route::post('/pengajuan/{type_slug}/{submission_id}/daftar-hadir', [DosenSubmissionPresenceController::class, 'store'])->name('pengajuan.presences.store');
        Route::put('/pengajuan/{type_slug}/{submission_id}/daftar-hadir/{id}', [DosenSubmissionPresenceController::class, 'update'])->name('pengajuan.presences.update');
        Route::delete('/pengajuan/{type_slug}/{submission_id}/daftar-hadir/{id}', [DosenSubmissionPresenceController::class, 'destroy'])->name('pengajuan.presences.destroy');

        Route::put('/pengajuan/{type_slug}/{submission_id}/publik-url-daftar-hadir', [DosenSubmissionPresenceController::class, 'updatePublicUrlPresence'])->name('pengajuan.presences.public-url.update');

        Route::get('/tahapan-akhir', [DosenFinalStepController::class, 'index'])->name('tahapan-akhir.index');
        Route::get('/tahapan-akhir/{student_id}', [DosenFinalStepController::class, 'show'])->name('tahapan-akhir.show');
    });

    Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('mahasiswa')->group(function () {
        Route::get('/pengajuan/{type_slug}', [MahasiswaSubmissionController::class, 'index'])->name('pengajuan.index');

        Route::get('/pengajuan/{type_slug}/{id}/berkas', [MahasiswaSubmissionController::class, 'showRequiredFiles'])->name('pengajuan.required_files.show');
        Route::put('/pengajuan/{type_slug}/{id}/berkas', [MahasiswaSubmissionController::class, 'updateRequiredFiles'])->name('pengajuan.required_files.update');

        Route::get('/pengajuan/{type_slug}/{id}/diskusi', [MahasiswaSubmissionController::class, 'showDiscussion'])->name('pengajuan.discussions.show');
        Route::post('/pengajuan/{type_slug}/{id}/diskusi', [MahasiswaSubmissionController::class, 'addDiscussion'])->name('pengajuan.discussions.add');

        Route::get('/pengajuan/{type_slug}/{id}/result', [MahasiswaSubmissionController::class, 'resultSubmission'])->name('pengajuan.result.show');

        Route::post('/pengajuan/{type_slug}/{id}/upload/result', [MahasiswaSubmissionController::class, 'uploadFileFinalDocument'])->name('pengajuan.result.upload');

        Route::get('/pengajuan/{type_slug}/{id}/revisi', [MahasiswaSubmissionController::class, 'revisiSubmission'])->name('pengajuan.revisi.show');
        Route::put('/pengajuan/{type_slug}/{id}/revisi', [MahasiswaSubmissionController::class, 'revisiSubmissionUpdate'])->name('pengajuan.revisi.update');

        Route::get('/pengajuan/{type_slug}/{id}/finish', [MahasiswaSubmissionController::class, 'finishSubmissionShow'])->name('pengajuan.finish.show');
        Route::put('/pengajuan/{type_slug}/{id}/finish', [MahasiswaSubmissionController::class, 'finishSubmissionUpdate'])->name('pengajuan.finish.update');

        Route::delete('/pengajuan/{type_slug}/{id}', [MahasiswaSubmissionController::class, 'destroy'])->name('pengajuan.destroy');

        // Route::post('/pengajuan/{type_slug}/upload-files', [MahasiswaSubmissionController::class, 'uploadLargeFile'])->name('pengajuan.upload-files');

        // doing upload and update files
        Route::post('/pengajuan/{type_slug}/upload', [MahasiswaSubmissionController::class, 'uploadFile'])->name('pengajuan.uploadFile');
        Route::post('/pengajuan/{type_slug}/{id}/update', [MahasiswaSubmissionController::class, 'updateFile'])->name('pengajuan.updateFile');

        Route::get('/tahapan-akhir', [MahasiswaFinalStepController::class, 'index'])->name('tahapan-akhir.index');
        Route::put('/tahapan-akhir', [MahasiswaFinalStepController::class, 'update'])->name('tahapan-akhir.update');
        Route::post('/tahapan-akhir/upload', [MahasiswaFinalStepController::class, 'upload'])->name('tahapan-akhir.upload');
    });

    Route::prefix('bagian-administrasi')->name('bagian-administrasi.')->middleware('bagian-administrasi')->group(function () {
        Route::get('/pengajuan/{type_slug}', [BagianAdministrasiSubmissionController::class, 'index'])->name('pengajuan.index');

        Route::get('/pengajuan/{type_slug}/{id}/berkas', [BagianAdministrasiSubmissionController::class, 'showRequiredFiles'])->name('pengajuan.required_files.show');
        Route::put('/pengajuan/{type_slug}/{id}/berkas', [BagianAdministrasiSubmissionController::class, 'updateRequiredFiles'])->name('pengajuan.required_files.update');

        Route::get('/pengajuan/{type_slug}/{id}/sidang', [BagianAdministrasiSubmissionController::class, 'showSchedule'])->name('pengajuan.schedule.show');
        Route::put('/pengajuan/{type_slug}/{id}/sidang', [BagianAdministrasiSubmissionController::class, 'updateSchedule'])->name('pengajuan.schedule.update');

        Route::get('/pengajuan/{type_slug}/{id}/penilaian', [BagianAdministrasiSubmissionController::class, 'showEvaluation'])->name('pengajuan.evaluation.show');

        // Route::get('/pengajuan/{type_slug}/{id}/presences', [BagianAdministrasiSubmissionController::class, 'presences'])->name('pengajuan.show.presences');

        Route::post('/pengajuan/{type_slug}/{id}/request-pembimbing', [BagianAdministrasiSubmissionController::class, 'requestPembimbing'])->name('pengajuan.request_pembimbing');
        Route::post('/pengajuan/{type_slug}/{id}/request-penguji', [BagianAdministrasiSubmissionController::class, 'requestPenguji'])->name('pengajuan.request_penguji');
        Route::post('/pengajuan/{type_slug}/{id}/bulk-schedule', [BagianAdministrasiSubmissionController::class, 'bulkSchedule'])->name('pengajuan.bulk_schedule');
        Route::post('/pengajuan/{type_slug}/{id}/{verificator_id}/request-approval', [BagianAdministrasiSubmissionController::class, 'requestApproval'])->name('pengajuan.request_approval');

        Route::get('/pengajuan/{type_slug}/{id}/result', [BagianAdministrasiSubmissionController::class, 'resultSubmission'])->name('pengajuan.result.show');

        Route::get('/pengajuan/{type_slug}/{id}/revisi', [BagianAdministrasiSubmissionController::class, 'revisiSubmission'])->name('pengajuan.revisi.show');

        Route::get('/pengajuan/{type_slug}/{id}/diskusi', [BagianAdministrasiSubmissionController::class, 'showDiscussion'])->name('pengajuan.discussions.show');
        Route::post('/pengajuan/{type_slug}/{id}/diskusi', [BagianAdministrasiSubmissionController::class, 'addDiscussion'])->name('pengajuan.discussions.add');

        Route::get('/tahapan-akhir', [BagianAdministrasiFinalStepController::class, 'index'])->name('tahapan-akhir.index');
        Route::get('/tahapan-akhir/{student_id}', [BagianAdministrasiFinalStepController::class, 'show'])->name('tahapan-akhir.show');
        Route::put('/tahapan-akhir/{student_id}', [BagianAdministrasiFinalStepController::class, 'update'])->name('tahapan-akhir.update');
    });

    Route::get('/pengajuan/{id}/download', [IndexController::class, 'submissionDownload'])->name('pengajuan.download');
    Route::get('/pengajuan/{id}/log', [IndexController::class, 'submissionLog'])->name('pengajuan.log');
});
