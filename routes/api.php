<?php

use App\Http\Controllers\Api\IoTGateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-WARGA Machine-to-Machine (M2M) IoT API Routes
|--------------------------------------------------------------------------
|
| Stateless endpoints without CSRF cookies for microcontrollers (ESP32,
| Arduino, Raspberry Pi) to interact with the Smart Gate ecosystem.
|
*/

Route::prefix('v1/gate')->group(function () {
    // 1. Heartbeat ping & check remote commands from mobile app
    Route::match(['get', 'post'], '/ping', [IoTGateController::class, 'ping'])->name('api.gate.ping');

    // 2. Scan card UID from MFRC522 RFID reader
    Route::post('/scan-rfid', [IoTGateController::class, 'scanRfid'])->name('api.gate.scan-rfid');

    // 3. Scan ticket QR from barcode/camera reader
    Route::post('/scan-qr', [IoTGateController::class, 'scanQr'])->name('api.gate.scan-qr');
});
