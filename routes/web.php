<?php

use App\Helpers\SpotaHelper;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SubmissionPresenceController as PublicSubmissionPresenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/sync-data-submissions-manually', function() {
//     $spotaHelper = new SpotaHelper();
//     $spotaHelper->syncSubmissionData();
//     return 'clear';
// });

Route::get('/data-pengajuan-tanpa-penguji', [IndexController::class, 'pengajuanSpotaTanpaPenguji'])->name('data-pengajuan-tanpa-penguji');

Route::get('/tesWA', [PublicSubmissionPresenceController::class, 'tesWA']);

Route::get('/downloadbaforce/{id}', [PublicSubmissionPresenceController::class, 'submissionDownloadForce'])->name('downloadbaforce.index');

Route::get('/', function () {
    return redirect('/login');
})->name('index');

Route::get('/isi-daftar-hadir/{type_slug}/{submission_id}', [PublicSubmissionPresenceController::class, 'index'])->name('isi-daftar-hadir.index');
Route::post('/isi-daftar-hadir/{type_slug}/{submission_id}', [PublicSubmissionPresenceController::class, 'store'])->name('isi-daftar-hadir.store');

require __DIR__.'/panel.php';

require __DIR__.'/auth.php';
