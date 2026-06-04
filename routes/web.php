<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\MicrosoftController;
use App\Http\Controllers\MetabaseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login/google', [AuthController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/usuario', function (Request $request) {
    return response()->json($request->user());
});

Route::get('/auth/microsoft', [MicrosoftController::class, 'redirect']);
Route::get('/auth/microsoft/callback', [MicrosoftController::class, 'callback']);

