<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\SatpamController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Route;

// Public Landing & Info
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'showLogin'])->name('login');
Route::post('/login', [HomeController::class, 'processLogin'])->name('login.post');
Route::post('/register-tenant', [HomeController::class, 'registerTenant'])->name('tenant.register');
Route::get('/quick-login/{role}', [HomeController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');
Route::post('/toggle-mode', [ScheduleController::class, 'toggleEnvironmentMode'])->name('environment.toggle');

// Pusat Kendali Super Admin Platform (Multi-Tenant Demo Hub)
Route::get('/super-admin', [SuperAdminController::class, 'index'])->name('superadmin.index');
Route::post('/super-admin/switch-tenant/{id}', [SuperAdminController::class, 'switchTenant'])->name('superadmin.switch-tenant');

// Dashboard Pengurus RT, RW, & Bendahara
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/verify-payment/{id}', [DashboardController::class, 'verifyPayment'])->name('dashboard.verify-payment');
Route::post('/dashboard/store-cashbook', [DashboardController::class, 'storeCashBook'])->name('dashboard.store-cashbook');
Route::post('/rw/verifikasi-warga/{id}/approve', [DashboardController::class, 'approveWargaVerification'])->name('rw.verifications.approve');
Route::post('/rw/verifikasi-warga/{id}/reject', [DashboardController::class, 'rejectWargaVerification'])->name('rw.verifications.reject');

// Console Pos Satpam
Route::get('/satpam', [SatpamController::class, 'console'])->name('satpam.console');
Route::post('/satpam/trigger-manual', [SatpamController::class, 'triggerManual'])->name('satpam.trigger-manual');
Route::post('/satpam/checkin-visitor', [SatpamController::class, 'checkInVisitor'])->name('satpam.checkin-visitor');

// Virtual Smart Gate Simulator
Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator');
Route::post('/simulator/trigger', [SimulatorController::class, 'triggerSimulation'])->name('simulator.trigger');
Route::get('/simulator/logs', [SimulatorController::class, 'getLatestLogs'])->name('simulator.logs');

// Panel Akses Warga Mandiri & Pengaturan Akun Terpisah
Route::get('/warga', [WargaController::class, 'index'])->name('warga.index');
Route::get('/pengaturan-akun', [WargaController::class, 'profile'])->name('warga.profile');
Route::post('/warga/open-gate', [WargaController::class, 'triggerOpenGate'])->name('warga.open-gate');
Route::post('/warga/create-visitor', [WargaController::class, 'createVisitorPass'])->name('warga.create-visitor');
Route::post('/warga/panic', [WargaController::class, 'panicAlert'])->name('warga.panic');
Route::post('/warga/profile', [WargaController::class, 'updateProfile'])->name('warga.profile.update');
Route::post('/warga/change-password', [WargaController::class, 'changePassword'])->name('warga.change-password');

// Modul Jadwal Keamanan (Satpam & Ronda Warga Bulan Ini)
Route::get('/jadwal-keamanan', [ScheduleController::class, 'index'])->name('schedules.index');
Route::post('/jadwal-keamanan', [ScheduleController::class, 'store'])->name('schedules.store');
Route::post('/jadwal-keamanan/{id}/checkin', [ScheduleController::class, 'checkIn'])->name('schedules.checkin');
Route::post('/jadwal-keamanan/{id}/tukar', [ScheduleController::class, 'requestSwap'])->name('schedules.swap');

// Modul Posyandu Digital (Tracking Balita, Anak, Ibu Hamil, Ibu, Lansia & Deteksi Stunting)
Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
Route::post('/posyandu/members', [PosyanduController::class, 'storeMember'])->name('posyandu.members.store');
Route::delete('/posyandu/members/{id}', [PosyanduController::class, 'destroyMember'])->name('posyandu.members.destroy');
Route::post('/posyandu/records', [PosyanduController::class, 'storeRecord'])->name('posyandu.records.store');

// Layanan Surat Pengantar RT Online
Route::get('/surat-pengantar', [LetterController::class, 'index'])->name('letters.index');
Route::post('/surat-pengantar', [LetterController::class, 'store'])->name('letters.store');
Route::post('/surat-pengantar/{id}/approve', [LetterController::class, 'approve'])->name('letters.approve');
Route::post('/surat-pengantar/{id}/reject', [LetterController::class, 'reject'])->name('letters.reject');
Route::get('/surat-pengantar/verifikasi/{code}', [LetterController::class, 'verify'])->name('letters.verify');

// Asisten AI Bang Warga (Tanya Jawab Lingkungan Interaktif)
Route::post('/ai/chat', [\App\Http\Controllers\Api\AiAssistantController::class, 'chat'])->name('ai.chat');
Route::get('/ai/suggestions', [\App\Http\Controllers\Api\AiAssistantController::class, 'suggestions'])->name('ai.suggestions');

// Halaman Fallback Ramah Pengguna (Coming Soon / Sedang Disiapkan)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
