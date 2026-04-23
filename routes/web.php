<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inicio');
});

Volt::route('/login', 'auth.login')->name('login')->middleware('guest');

Route::middleware(['auth'])->group(function () {
    Volt::route('/inicio', 'dashboard.dashboard-index')->name('inicio');
    Volt::route('/cuadrante', 'schedule.schedule-index')->name('schedule');
    Volt::route('/amarillos-arancalo', 'schedule.schedule-index')->name('amarillos.arancalo');
    Volt::route('/cima', 'schedule.schedule-index')->name('cima');
    Volt::route('/amarillos-cima', 'schedule.schedule-index')->name('amarillos.cima');
    Volt::route('/administracion-arancalo', 'administration.administration-index')->name('administration.arancalo');
    Volt::route('/administracion-cima', 'administration.administration-index')->name('administration.cima');
    
    Route::post('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
