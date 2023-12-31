<?php
use App\Http\Controllers\Api\LoginController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('loginWithSocialMedia', [LoginController::class, 'loginWithSocialMedia']);
Route::group(['middleware' => ['web']], function () {
    Route::get('/login/google', [LoginController::class, 'redirectToGoogle']);
    Route::get('/login/google/callback', [LoginController::class, 'handleGoogleCallback']);
});

