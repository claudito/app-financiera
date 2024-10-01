<?php

use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('cuentas', [ AccountController::class, 'index']);
Route::post('cuentas/{id}/depositar', [ AccountController::class, 'depositar']);
Route::post('cuentas/{id}/retirar', [ AccountController::class, 'retirar']);
Route::post('cuentas/{id}/transferir', [ AccountController::class, 'transferir']);