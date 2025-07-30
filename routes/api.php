<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppAppointmentController;
use App\Http\Controllers\Api\QueuedAppointmentsController;
use App\Http\Controllers\Api\TelevisionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Television API endpoints - with explicit CORS handling
Route::group(['middleware' => ['cors']], function () {
    // Returns user configuration data
    Route::get('/television/user-config', [TelevisionController::class, 'getUserConfigByEmail']);
    Route::get('/television/user-config/{email}', [TelevisionController::class, 'getUserConfigByEmail']);
    
    // Returns queued appointments
    Route::get('/television/queued-appointments', [TelevisionController::class, 'getQueuedAppointmentsByEmail']);
    Route::get('/television/queued-appointments/{email}', [TelevisionController::class, 'getQueuedAppointmentsByEmail']);
    
    // Returns the actual image data, can be used directly in <img> tags
    // Example: <img src="http://yourserver.com/api/television/header-image/user@example.com" />
    Route::get('/television/header-image', [TelevisionController::class, 'getHeaderImageByEmail']);
    Route::get('/television/header-image/{email}', [TelevisionController::class, 'getHeaderImageByEmail']);
    
    // Creates a sample header image for testing
    Route::post('/television/create-sample-image', [TelevisionController::class, 'createSampleImage']);
    
    // Test broadcast endpoint
    Route::post('/television/test-broadcast', [TelevisionController::class, 'testBroadcast']);
});

Route::get("/user/healthcare/configuration/{id}", "App\Http\Controllers\Api\ClientController@getConfiguration")->name("client_configuration");
// Route::post("/user/check/mobile", "App\Http\Controllers\Api\AuthController@checkMobile")->name("check_mobile");
// Route::post("/user/generate/token", "App\Http\Controllers\Api\AuthController@verifyOtp")->name("generate_token");

// Route::middleware(['auth:sanctum', 'ability:patient-app'])->prefix("user")->group(function () {
//     Route::get("/lab/reports", "App\Http\Controllers\Api\ReportController@getRecords")->name("get_reports")->middleware(['throttle:50,1']);;
//     Route::get("/doctor/prescriptions", "App\Http\Controllers\Api\PrescriptionController@getRecords")->name("get_prescription")->middleware(['throttle:50,1']);;
//     Route::get("/get/doctors", "App\Http\Controllers\Api\DoctorController@getDoctors")->name("get_doctors")->middleware(['throttle:50,1']);;
//     Route::get("/get/accounts", "App\Http\Controllers\Api\AccountController@getAccounts")->name("get_accounts")->middleware(['throttle:50,1']);;
//     Route::get("/doctor/availabledays/{type}/{id}", "App\Http\Controllers\Api\DoctorController@getDoctorAvailableDays")->name("available_days")->middleware(['throttle:50,1']);;
//     Route::post("/create/person", "App\Http\Controllers\Api\AccountController@createPerson")->name("create_account");
//     Route::post("/delete/person", "App\Http\Controllers\Api\AccountController@deletePerson")->name("create_account");
//     Route::post("/create/appointment", "App\Http\Controllers\Api\AppointmentController@createAppointment")->name("create_appointment");
//     Route::post("/get/doctor/availableslots/{did}/{type}/{day}/{date}", "App\Http\Controllers\Api\DoctorController@getAvailableSlots")->name("available_slots");
//     Route::post("/create/payment/session", "App\Http\Controllers\Api\CFGatewayController@createSession")->name("gateway_session");
//     Route::post("/verify/payment", "App\Http\Controllers\Api\CFGatewayController@verifyPayment")->name("verify_payment");
//     Route::post("/document/file/upload", "App\Http\Controllers\Api\UserDocumentController@uploadFile")->name("document_upload");
//     Route::post("/document/save", "App\Http\Controllers\Api\UserDocumentController@saveDocument")->name("document_save");
//     Route::get("/get/user/documents", "App\Http\Controllers\Api\UserDocumentController@getRecords")->name("get_records");
//     Route::get("/get/appointments", "App\Http\Controllers\Api\AppointmentController@getAppointments")->name("get_appointments");
//     Route::post("/user/appointment/checkin/{id}", "App\Http\Controllers\Api\AppointmentController@checkIn")->name("check_in");
//     Route::post("/user/appointment/cancel/{id}", "App\Http\Controllers\Api\AppointmentController@cancelAppointment")->name("check_in");
// });

Route::post("client/generate/token", "App\Http\Controllers\Api\AuthController@generateToken")->name("create_token");

Route::middleware(['auth:sanctum', 'ability:client-app'])->prefix("client")->group(function () {
    Route::post("/user/me", "App\Http\Controllers\Api\AuthController@getUserProfile")->name("user_me");
    Route::post("/fetch/upcoming/leaves", "App\Http\Controllers\Api\DoctorLeaveController@fetchUpcomingLeaves")->name("fetch_leaves");
    Route::post("/create/leave", "App\Http\Controllers\Api\DoctorLeaveController@createLeave")->name("create_leave");
    Route::post("/toggle/doctor/leave", "App\Http\Controllers\Api\DoctorLeaveController@toggleStatus")->name("toggle_status");
    Route::post("/leave/delete", "App\Http\Controllers\Api\DoctorLeaveController@deleteLeave")->name("delete_leave");
    Route::post("/get/appointments", "App\Http\Controllers\Api\WhatsAppAppointmentController@getAppointments")->name("whatsapp_get_appointments");
    Route::post("/save/appointment", "App\Http\Controllers\Api\WhatsAppAppointmentController@saveAppointment")->name("appointment_save");
    Route::post("/whatsapp/appointment", [WhatsAppAppointmentController::class, 'apiCreateOrUpdateAppointment'])->name("whatsapp_appointment_api");
    Route::get("/get/hospitals/filter", "App\Http\Controllers\Api\WhatsAppAppointmentController@getHospitalsFilter")->name("hospitals_filter");
    Route::get("/get/doctors/filter", "App\Http\Controllers\Api\WhatsAppAppointmentController@getDoctorsFilter")->name("doctors_filter");
    Route::get("/get/timerange/filter/{doctorid}", "App\Http\Controllers\Api\WhatsAppAppointmentController@getTimeRangeFilter")->name("time_range_filter");
    Route::post("/get/doctors", "App\Http\Controllers\Api\WhatsAppAppointmentController@getDoctors")->name("get_doctors");
    Route::post("/get/appointments/count", "App\Http\Controllers\Api\WhatsAppAppointmentController@getAppointmentCounts")->name("get_appointments_counts");
    Route::post("/update/status", "App\Http\Controllers\Api\WhatsAppAppointmentController@updateAppointmentStatus")->name("update_appointment_status");
    Route::post("/cancel/appointment", "App\Http\Controllers\Api\WhatsAppAppointmentController@cancelAppointment")->name("cancel_appointment");
    Route::post("/doctor/available/dates", "App\Http\Controllers\Api\WhatsAppAppointmentController@doctorAvailableDates")->name("doctor_available_dates");
    Route::post("/doctor/available/timeslots", "App\Http\Controllers\Api\WhatsAppAppointmentController@doctorAvailableTimeSlots")->name("doctor_available_slots");
    Route::post("/doctor/available/timeranges", "App\Http\Controllers\Api\WhatsAppAppointmentController@doctorAvailableTimeRanges")->name("doctor_available_ranges");
    Route::post("/followup/create", "App\Http\Controllers\Api\WhatsAppAppointmentController@createFollowup")->name("create_follow_up");
    Route::post("/get/flow/appointment", "App\Http\Controllers\Api\WhatsAppAppointmentController@getScreenFlowAppointment")->name("get_flow_appointment");
    Route::post("/update/flow/appointment", "App\Http\Controllers\Api\WhatsAppAppointmentController@updateAppointmentStatusReturnNextAppointment")->name("update_flow_appointment");
    Route::get("/fetch/followups", "App\Http\Controllers\Api\WhatsAppAppointmentController@getFollowUps")->name("fetch_followups");
    Route::post("/update/patientid", "App\Http\Controllers\Api\WhatsAppAppointmentController@updatePatientId")->name("update_patient_id");
    Route::post("/fetch/weekly/timeranges", "App\Http\Controllers\Api\WhatsAppAppointmentController@fetchTimeRanges")->name("fetch_app_timeranges");
    Route::post("/save/weekly/timeranges", "App\Http\Controllers\Api\WhatsAppAppointmentController@saveWeeklyTimeRanges")->name("fetch_app_timeranges");
    Route::delete('/followup/delete', [WhatsAppAppointmentController::class, 'deleteFollowup']);
    Route::post('/set/priority', [WhatsAppAppointmentController::class, 'setPriority']);
    Route::get("/get/queued-appointments", [QueuedAppointmentsController::class, 'getQueuedAppointments']);
});


Route::get('/test', function (Request $request) {
    return "hii";
});

Route::get("/user/person/join/call", "App\Http\Controllers\Api\ConferenceController@getVideoCallPage")->name("person_video_call");
Route::get("/user/doctor/join/call", "App\Http\Controllers\Api\ConferenceController@getVideoCallPage")->name("join_video_call");

Route::get("/user/get/dotor/checkins", "App\Http\Controllers\Api\ConferenceController@getDoctorCheckins")->name("get_doctor_checkins");
Route::get("/user/get/checkins/{id}", "App\Http\Controllers\Api\ConferenceController@getCheckins")->name("get_checkins");

