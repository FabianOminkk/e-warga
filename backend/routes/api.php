<?php

use App\Http\Controllers\Api\AiAssistantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\IoTGateController;
use App\Http\Controllers\Api\LetterApiController;
use App\Http\Controllers\Api\PosyanduApiController;
use App\Http\Controllers\Api\SatpamApiController;
use App\Http\Controllers\Api\ScheduleApiController;
use App\Http\Controllers\Api\WargaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-WARGA Backend REST API Routes (API-First Headless Layer)
|--------------------------------------------------------------------------
|
| Stateless REST API JSON endpoints designed for:
| 1. Mobile Apps (Flutter / React Native for Android & iOS)
| 2. Decoupled Frontend SPAs (Vue / React / Next.js)
| 3. IoT Hardware & Controllers (ESP32 Smart Gate, RFID, Barcode)
|
*/

Route::prefix('v1')->group(function () {

    // --- A. PUBLIC & AUTHENTICATION ENDPOINTS ---
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::get('/letters/verify/{code}', [LetterApiController::class, 'verify'])->name('api.letters.verify');

    // --- B. IOT HARDWARE MACHINE-TO-MACHINE (M2M) ---
    Route::prefix('gate')->group(function () {
        Route::match(['get', 'post'], '/ping', [IoTGateController::class, 'ping'])->name('api.gate.ping');
        Route::post('/scan-rfid', [IoTGateController::class, 'scanRfid'])->name('api.gate.scan-rfid');
        Route::post('/scan-qr', [IoTGateController::class, 'scanQr'])->name('api.gate.scan-qr');
    });

    // --- C. AI ASSISTANT (BANG WARGA AI INTERACTIVE ENGINE) ---
    Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->name('api.ai.chat');
    Route::get('/ai/suggestions', [AiAssistantController::class, 'suggestions'])->name('api.ai.suggestions');

    // --- D. PROTECTED BUSINESS APIS (BEARER TOKEN REQUIRED) ---
    Route::middleware('auth.api')->group(function () {

        // 1. Session & Profil Pengguna
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

        // 2. Dashboard Eksekutif & Kas Keuangan (Pengurus RT/RW/Bendahara)
        Route::get('/dashboard/summary', [DashboardApiController::class, 'summary'])->name('api.dashboard.summary');
        Route::get('/dashboard/cashbook', [DashboardApiController::class, 'cashbook'])->name('api.dashboard.cashbook');
        Route::post('/dashboard/cashbook', [DashboardApiController::class, 'storeCashBook'])->name('api.dashboard.cashbook.store');
        Route::post('/dashboard/payments/{id}/verify', [DashboardApiController::class, 'verifyPayment'])->name('api.dashboard.payments.verify');
        Route::get('/dashboard/verifications', [DashboardApiController::class, 'verifications'])->name('api.dashboard.verifications');
        Route::post('/dashboard/verifications/{id}/approve', [DashboardApiController::class, 'approveVerification'])->name('api.dashboard.verifications.approve');
        Route::post('/dashboard/verifications/{id}/reject', [DashboardApiController::class, 'rejectVerification'])->name('api.dashboard.verifications.reject');

        // 3. Portal Warga Mandiri (Mobile App Resident)
        Route::get('/warga/portal', [WargaApiController::class, 'portal'])->name('api.warga.portal');
        Route::post('/warga/open-gate', [WargaApiController::class, 'triggerOpenGate'])->name('api.warga.open-gate');
        Route::post('/warga/visitors', [WargaApiController::class, 'createVisitor'])->name('api.warga.visitors.create');
        Route::post('/warga/panic', [WargaApiController::class, 'panicAlert'])->name('api.warga.panic');
        Route::get('/warga/bills', [WargaApiController::class, 'bills'])->name('api.warga.bills');
        Route::post('/warga/profile', [WargaApiController::class, 'updateProfile'])->name('api.warga.profile.update');
        Route::post('/warga/change-password', [WargaApiController::class, 'changePassword'])->name('api.warga.change-password');

        // 4. Pos Jaga & Konsol Satpam (Security Tablet/Mobile)
        Route::get('/satpam/console', [SatpamApiController::class, 'console'])->name('api.satpam.console');
        Route::post('/satpam/trigger-manual', [SatpamApiController::class, 'triggerManual'])->name('api.satpam.trigger-manual');
        Route::post('/satpam/checkin-visitor', [SatpamApiController::class, 'checkInVisitor'])->name('api.satpam.checkin-visitor');

        // 5. Jadwal Keamanan & Siskamling Ronda
        Route::get('/schedules', [ScheduleApiController::class, 'index'])->name('api.schedules.index');
        Route::post('/schedules', [ScheduleApiController::class, 'store'])->name('api.schedules.store');
        Route::post('/schedules/{id}/checkin', [ScheduleApiController::class, 'checkIn'])->name('api.schedules.checkin');
        Route::post('/schedules/{id}/swap', [ScheduleApiController::class, 'requestSwap'])->name('api.schedules.swap');
        Route::post('/schedules/toggle-mode', [ScheduleApiController::class, 'toggleMode'])->name('api.schedules.toggle-mode');

        // 6. Posyandu Digital & Deteksi Dini Stunting
        Route::get('/posyandu', [PosyanduApiController::class, 'index'])->name('api.posyandu.index');
        Route::post('/posyandu/members', [PosyanduApiController::class, 'storeMember'])->name('api.posyandu.members.store');
        Route::post('/posyandu/records', [PosyanduApiController::class, 'storeRecord'])->name('api.posyandu.records.store');
        Route::delete('/posyandu/members/{id}', [PosyanduApiController::class, 'destroyMember'])->name('api.posyandu.members.destroy');

        // 7. Surat Pengantar RT Online
        Route::get('/letters', [LetterApiController::class, 'index'])->name('api.letters.index');
        Route::post('/letters', [LetterApiController::class, 'store'])->name('api.letters.store');
        Route::post('/letters/{id}/approve', [LetterApiController::class, 'approve'])->name('api.letters.approve');
        Route::post('/letters/{id}/reject', [LetterApiController::class, 'reject'])->name('api.letters.reject');
    });
});
