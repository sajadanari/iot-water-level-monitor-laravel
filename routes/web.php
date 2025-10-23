<?php

use App\Livewire\WaterLevelDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', WaterLevelDashboard::class)->name('home');
