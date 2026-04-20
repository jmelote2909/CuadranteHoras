<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inicio');
});

Volt::route('/inicio', 'dashboard.dashboard-index')->name('inicio');
Volt::route('/cuadrante', 'schedule.schedule-index')->name('schedule');
Volt::route('/amarillos-arancalo', 'schedule.schedule-index')->name('amarillos.arancalo');
Volt::route('/cima', 'schedule.schedule-index')->name('cima');
Volt::route('/amarillos-cima', 'schedule.schedule-index')->name('amarillos.cima');
