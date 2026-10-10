<?php

use App\Http\Controllers\Api\SubmissionController as ApiSubmissionController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/sync-data-submission', [ApiSubmissionController::class, 'syncSubmissionData']);
Route::post('/submission/update', [ApiSubmissionController::class, 'updateDetailSubmission']);

// User
Route::get('/user/index', [ApiUserController::class, 'index']);
Route::get('/gettamahasiswabynim/{nim}', [ApiUserController::class, 'getDataTAByNim']);
Route::post('/user/update', [ApiUserController::class, 'update']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
