<?php

use App\Http\Controllers\RPayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppFlowController;
use App\Http\Controllers\WhatsAppConnectController;
use App\Http\Controllers\SharedDocumentController;
use App\Http\Controllers\Controller;
use App\Livewire\AppointmentBooking;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect("admin/login");
});

Route::post('/waflow/{uuid}', [
    WhatsAppFlowController::class,
    'index',
])->name('waflow.index');

Route::get('/test/encryption', [
    Controller::class,
    'testEncryption',
])->name('test.encryption');

Route::post('/wa/actions/{vendorid}', [
    WhatsAppConnectController::class,
    'handleMessage',
])->name('wa.actions');

Route::get('/wa/appointment/{clientuid}/{appointmentuid}', AppointmentBooking::class);
Route::get('/rpay/appointment/callback/', [RPayController::class, "callback"]);
Route::post('/rpay/webhook/', [RPayController::class, "webHook"]);
Route::get("/enc/{vendorid}/{file}", [SharedDocumentController::class, "downloadDocument"]);
Route::post("/enc/wa/{vendorid}/{file}", [SharedDocumentController::class, "downloadDocumentOnWhatsApp"]);

// Add debug route
Route::get('/debug-uploads', function () {
    return view('debug-uploads');
});

