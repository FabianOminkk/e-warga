<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SatpamController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Route;

// Public Landing & Info
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/register-tenant', [HomeController::class, 'registerTenant'])->name('tenant.register');
Route::get('/quick-login/{role}', [HomeController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

// Dashboard Pengurus RT & Bendahara
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/verify-payment/{id}', [DashboardController::class, 'verifyPayment'])->name('dashboard.verify-payment');
Route::post('/dashboard/store-cashbook', [DashboardController::class, 'storeCashBook'])->name('dashboard.store-cashbook');

// Console Pos Satpam
Route::get('/satpam', [SatpamController::class, 'console'])->name('satpam.console');
Route::post('/satpam/trigger-manual', [SatpamController::class, 'triggerManual'])->name('satpam.trigger-manual');
Route::post('/satpam/checkin-visitor', [SatpamController::class, 'checkInVisitor'])->name('satpam.checkin-visitor');

// Virtual Smart Gate Simulator
Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator');
Route::post('/simulator/trigger', [SimulatorController::class, 'triggerSimulation'])->name('simulator.trigger');
Route::get('/simulator/logs', [SimulatorController::class, 'getLatestLogs'])->name('simulator.logs');

// Panel Akses Warga (Mobile View)
Route::get('/warga', [WargaController::class, 'index'])->name('warga.index');
Route::post('/warga/open-gate', [WargaController::class, 'triggerOpenGate'])->name('warga.open-gate');
Route::post('/warga/create-visitor', [WargaController::class, 'createVisitorPass'])->name('warga.create-visitor');
Route::post('/warga/panic', [WargaController::class, 'panicAlert'])->name('warga.panic');
