<?php

use App\Http\Controllers\chatgptController;
use App\Http\Controllers\webhookController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook', [webhookController::class, 'validar']);
Route::post('/webhook', [webhookController::class, 'recibir']);
Route::get('/chatgpt', [chatgptController::class, 'datosChat']);
