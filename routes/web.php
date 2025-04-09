<?php

use App\Http\Controllers\AmoCRMController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AmoCRMController::class, 'index'])->name('home');

Route::get('/amocrm/login', [AmoCRMController::class, 'login'])->name('amocrm.login');
Route::get('/amocrm/logout', [AmoCRMController::class, 'logout'])->name('amocrm.logout');

Route::get('/amocrm/callback', [AmoCRMController::class, 'callback'])->name('amocrm.callback');
Route::get('/amocrm/get_leads', [AmoCRMController::class, 'get_lead'])->name('amocrm.get_leads');
