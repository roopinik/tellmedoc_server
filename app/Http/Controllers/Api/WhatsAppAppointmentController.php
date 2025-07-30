<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\HealthCareUser;
use App\Models\WhatsAppAppointment;
use App\Models\Followup;
use App\Models\Hospital;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Services\TDAuthService;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\WAConnectService;
use Carbon\Carbon;

class WhatsAppAppointmentController extends BaseController
{
    public $role = "doctor";
    public $user;
    public $authService;
    public $clientId;
    public $format = "Y-m-d";
    public function __construct()
    {
        $this->authService = new TDAuthService();
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            $this->clientId = $this->user->client_id;
            $this->role = $this->authService->getRole($this->user);
            return $next($request);
        });
    }

    public function getAppointments()
    {
        $user = auth()->user();
        $client = $user->client;
        $itemsCount = request("count", 100);
        $status = request("status", "All");
        $doctorId = request("doctor_id");
        $hospitalId = request("hospital_id");
        $query = $this->getQuery();
        $query = $this->setDateQuery($query);
        $query = $this->setSearchQuery($query);
        $query->where("payment_status", "PAYMENT_COMPLETED");
        if ($status != "All") {
            $query->where("status", $status);
            if ($status != "Cancelled") {
                $query->whereNot("status", "Cancelled");
            }
        }
        $query->where("doctor_id", $doctorId);
        $query->where("hospital_id", $hospitalId);
        $query->orderBy("appointment_date", "asc");
        if ($client->priority != 3)
            $query->orderBy("appointment_time", "asc");
        if ($status == 'Queued') {
            $query->orderBy("priority", "asc");
            $query->orderBy("token", "asc");
        }
        $appointments = $query
            ->select(
                "id",
                "patient_name",
                "appointment_date",
                "appointment_time",
                "appointment_end_time",
                "booking_whatsapp_number as contact_number",
                "doctor_id",
                "created_at",
                "uuid",
                "status",
                "token",
                "priority",
                "patient_id",
                "hospital_id"
            )
            ->with([
                "doctor" => function ($query) {
                    $query->select(
                        "id",
                        "name_translated->en as doctor_name",
                        "profile_pic"
                    );
                },
                "doctor.specializations" => function ($query) {
                    $query->select(
                        "specializations.id",
                        "name->en as specialization_name"
                    );
                }
            ]);

        return $appointments->paginate($itemsCount);
    }

    public function getDoctorsFilter()
    {
        $user = auth()->user();
        $clientId = $user->client_id;
        $doctors = HealthCareUser::where("client_id", $clientId)
            ->select(["id", "name_translated->en as label"])
            ->whereHas(
                "roles",
                function ($q) {
                    $q->where('name', 'doctor');
                }
            )->orderBy('first_name')->get();
        return response()->json($doctors);
    }

    public function getHospitalsFilter()
    {
        $user = auth()->user();
        $clientId = $user->client_id;
        $hospitals = Hospital::select(["id", 'name->en as name_en'])->where("client_id", $clientId)->get();
        return response()->json($hospitals);
    }

    public function getTimeRangeFilter()
    {
        $doctorid = request("doctorid");
        $doctor = HealthCareUser::find($doctorid);
        $slots = $doctor->appointment_slots;
        $currentDay = date("l");
        $slots = collect($slots)->filter(fn($slot) => $slot["weekDay"] == "Wednesday");
        $slots = $slots->map(fn($slot) => [
            "doctor" => $doctorid,
            "hospital" => (int) $slot['hospital'],
            "timerange" => date("g:i A", strtotime($slot['startTime'])) . " - " . date("g:i A", strtotime($slot['endTime']))
        ]);
        return $slots;
    }

    public function getDoctors()
    {
        $user = auth()->user();
        $clientId = $user->client_id;
        $search = request("search", null);
        $query = HealthCareUser::where("client_id", $clientId)
            ->select(["id", "name_translated->en as doctor_name", "profile_pic"])
            ->with([
                "specializations" => function ($query) {
                    $query->select(
                        "specializations.id",
                        "name->en as specialization_name"
                    );
                }
            ])
            ->whereHas(
                "roles",
                function ($q) {
                    $q->where('name', 'doctor');
                }
            )->orderBy('first_name');

        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where("mobile_number", "like", "%$search%")
                    ->orWhere("first_name", "like", "%$search%")
                    ->orWhere("last_name", "like", "%$search%");
            });
        }
        $doctors = $query->get();
        return response()->json($doctors);
    }

    public function saveAppointment()
    {
        $data = request()->all();
        $waFlowService = new \App\Services\WAFlowService;
        $appointment = $waFlowService->createAppointmentFromMobileApp($data);
        return response()->json($appointment);
    }

    public function createToken($appointment)
    {
        if (auth()->user()->client_id != $appointment->client_id) {
            return abort(401, "Unauthorized.");
        }
        // if ($appointment->token != null) {
        //     return $appointment->token;
        // }
        $date = $appointment->appointment_date;
        $doctor = $appointment->doctor_id;
        $hospital = $appointment->hospital_id;
        $query = "select max(token) as token from whats_app_appointments where
        appointment_date ='$date'
        and hospital_id = '$hospital'
        and doctor_id = '$doctor'
        ";
        $token = DB::select($query)[0]->token;
        if ($token == null)
            $token = 1;
        else {
            $token = $token + 1;
        }
        return $token;
    }

    public function setSearchQuery($query)
    {
        $search = request("search_query", null);
        $whatsAppNumber = request("contact_number", null);
        if ($whatsAppNumber != null) {
            $query->where("booking_whatsapp_number", $whatsAppNumber);
        }
        if ($search == null)
            return $query;
        $query->where(function ($q) use ($search) {
            $q->where("booking_whatsapp_number", "like", "%$search%")
                ->orWhere("patient_name", "like", "%$search%");
        });
        return $query;
    }

    public function doctorAvailableDates()
    {
        $doctorId = request("doctor_id", null);
        $hospitalId = request("hospital_id", null);
        if ($doctorId == null)
            return response()->json(["error" => "Doctor ID is required"], 400);
        $doctor = HealthCareUser::find($doctorId);
        $dates = $doctor->availableDates("offline", $hospitalId);
        return response()->json($dates);
    }

    public function doctorAvailableTimeSlots()
    {
        $doctorId = request("doctor_id", null);
        $date = request("date", null);
        if ($doctorId == null || $date == null)
            return response()->json(["error" => "Bad Data"], 400);
        $doctor = HealthCareUser::find($doctorId);
        $timeSlots = $doctor->getAvailableTimeSlots($date, "offline");
        $data = [];
        foreach ($timeSlots as $slot) {
            $data[] = $slot;
        }
        return response()->json($data);
    }

    public function fetchTimeRanges()
    {
        $doctorId = auth()->user()->id;
        $doctor = HealthCareUser::find($doctorId);
        if (!$doctor->hasRole("doctor"))
            return response()->json(["error" => "Unauthorized"], 401);
        $timeRanges = $doctor->appointment_slots;
        return response()->json($timeRanges);
    }

    public function saveWeeklyTimeRanges()
    {
        $doctorId = auth()->user()->id;
        $doctor = HealthCareUser::find($doctorId);
        if (!$doctor->hasRole("doctor"))
            return response()->json(["error" => "Unauthorized"], 401);
        $doctor->appointment_slots = request("timeSlots");
        $doctor->save();
        return response()->json(["success" => true]);
    }

    public function doctorAvailableTimeRanges()
    {
        $doctorId = request("doctor_id", null);
        $date = request("date", null);
        $hospitalid = request("hospital_id", null);
        if ($doctorId == null || $date == null)
            return response()->json(["error" => "Bad Data"], 400);
        $doctor = HealthCareUser::find($doctorId);
        $timeranges = $doctor->getAvailableTimeRanges($date, $hospitalid, true);
        return response()->json($timeranges);
    }

    public function getAppointmentCounts()
    {
        $format = $this->format;
        $now = new \DateTime();
        $today = $now->format($format);
        $now->modify("+1 day");
        $tommorow = $now->format($format);
        $now->modify("+6 day");
        $next7thDay = $now->format($format);
        $now->modify("-14 day");
        $prev7thDay = $now->format($format);
        $query = $this->getQuery();
        $query->whereNot("status", "Cancelled");
        $query->where("payment_status", "PAYMENT_COMPLETED");
        $todaysCount = $query->where("appointment_date", "=", $today)->count();
        $query = $this->getQuery();
        $query->whereNot("status", "Cancelled");
        $query->where("payment_status", "PAYMENT_COMPLETED");
        $tommorowsCount = $query->where("appointment_date", "=", $tommorow)->count();
        $query = $this->getQuery();
        $query->whereNot("status", "Cancelled");
        $query->where("payment_status", "PAYMENT_COMPLETED");
        $query->where("appointment_date", ">=", $prev7thDay);
        $query->where("appointment_date", "<=", $today);
        $prev7dayCount = $query->count();
        $query = $this->getQuery();
        $query->whereNot("status", "Cancelled");
        $query->where("payment_status", "PAYMENT_COMPLETED");
        $query->where("appointment_date", ">=", $today);
        $query->where("appointment_date", "<=", $next7thDay);
        $next7dayCount = $query->count();
        return [
            "today" => $todaysCount,
            "tommorow" => $tommorowsCount,
            "prev7days" => $prev7dayCount,
            "next7days" => $next7dayCount,
        ];
    }

    public function setDateQuery($query)
    {
        $format = $this->format;
        $now = new \DateTime();
        $dateFrom = request("from_date", null);
        $dateTo = request("to_date", null);
        $dateQuery = request("date_query", null);
        if ($dateFrom != null) {
            $query->where("appointment_date", ">=", $dateFrom);
            $query->where("appointment_date", "<=", $dateTo);
            return $query;
        }
        if ($dateQuery != null) {
            if ($dateQuery == "today") {
                $dateFrom = $now->format($format);
                $dateTo = $dateFrom;
            } else if ($dateQuery == "tommorow") {
                $now->modify("+1 day");
                $dateFrom = $now->format($format);
                $dateTo = $dateFrom;
            } else if ($dateQuery == "next7days") {
                $dateFrom = $now->format($format);
                $now->modify("+7 day");
                $dateTo = $now->format($format);
            } else if ($dateQuery == "prev7days") {
                $dateTo = $now->format($format);
                $now->modify("-7 day");
                $dateFrom = $now->format($format);
            }
        } else if ($dateFrom == null) {
            return $query;
        }
        $query->where("appointment_date", ">=", $dateFrom);
        $query->where("appointment_date", "<=", $dateTo);
        return $query;
    }

    public function getQuery()
    {
        return WhatsAppAppointment::where("client_id", $this->clientId);
    }

    public function getFollowUps()
    {
        $count = request("count");
        $query = Followup::with(["doctor:id,first_name,last_name"])
            ->orderBy('created_at', 'desc');
        $search = request('search', false);
        if ($search != false)
            $query->where(function ($query) use ($search) {
                $query->where('name', "like", "%$search%")
                    ->orWhere('mobilenumber', "like", "%$search%");
            });
        return $query->paginate($count);
    }

    public function updateAppointmentStatus()
    {
        $appointment = $this->getQuery()->where("id", request("id"))->first();
        $user = auth()->user();
        $value = request("value");
        if ($appointment == null || $appointment->client_id != $user->client_id) {
            return response()->json(["error" => "Appointment not found"], 404);
        }
        if ($value == "Queued") {
            $appointment->token = $this->createToken($appointment);
            $appointment->status = "Queued";
            if (request("patient_id", false) != false) {
                $appointment->patient_id = request("patient_id");
            }
            $ct = date('H:i');
            $current_time = strtotime($ct);
            $start_time = strtotime($appointment->appointment_time);
            $end_time = strtotime($appointment->appointment_end_time);
            if ($start_time > $end_time) {
                $end_time = strtotime("$end_time +1 day"); // Adjust end time to next day
            }
            if (($current_time <= $end_time) && $appointment->priority == 3) {
                if ($appointment->walk_in != true)
                    $appointment->priority = 2;
            }
            $appointment->save();
        }

        $appointment->status = $value;
        $appointment->save();
        return response()->json($appointment);
    }

    public function updateAppointmentStatusReturnNextAppointment()
    {
        $appointmentId = request("appointment_id");
        $user = auth()->user();
        $value = request("value");
        $appointment = WhatsAppAppointment::find($appointmentId);
        $hospitalId = $appointment->hospital_id;
        if ($appointment == null || $appointment->doctor_id != $user->id)
            return response()->json(["error" => "Appointment not found"], 404);
        $appointment->status = $value;
        $appointment->save();
        $waFlowService = new \App\Services\WAFlowService;
        $data = [
            "doctor_id" => $appointment->doctor_id,
            "hospital_id" => $appointment->hospital_id,
            "status" => $value,
            "appointment_date" => "2025-02-18",
            "token" => $appointment->token,
            "created_by" => $user->id
        ];
        $collectionName = $name = "live_stream_" . $appointment->client_id;
        $service = new \App\Services\LiveUpdatesService;
        $record = $waFlowService->getFlowAppointment($hospitalId);
        $service->createRecord($collectionName, $data);
        if (count($record) > 0)
            return response()->json($record[0]);
        return response()->json(null);
    }

    public function getScreenFlowAppointment()
    {
        $waFlowService = new \App\Services\WAFlowService;
        $hospitalId = request("hospital_id");
        $record = $waFlowService->getFlowAppointment($hospitalId);
        if (count($record) > 0)
            return response()->json($record[0]);
        return response()->json(null);
    }

    public function cancelAppointment()
    {
        $appointment = $this->getQuery()->where("uuid", request("uuid"))->first();
        if ($appointment == null)
            return response()->json(["error" => "Appointment not found"], 404);
        $appointment->status = "Cancelled";
        $appointment->save();
        try {
            $this->sendWACancelMessage($appointment);
        } catch (\Exception $e) {
        }

        return response()->json($appointment);
    }

    public function sendWACancelMessage($appointment)
    {
        $client = Client::find($appointment->client_id);
        $data = [
            [
                "mobiles" => $appointment->booking_whatsapp_number,
                "patientname" => $appointment->patient_name,
                "doctorname" => $appointment->doctor->name_translated,
                "appointmentdate" => Carbon::createFromFormat(
                    "Y-m-d H:i",
                    $appointment->appointment_date . " " . $appointment->appointment_time
                )->format("d M h:i A")
            ]
        ];

        if ($client->notification_mode === 'whatsapp') {
            $waService = new WAConnectService();
            $waService->sendTemplateMessage(
                $client,
                $appointment->booking_whatsapp_number,
                'send_cancel_reminder',
                [
                    'field_1' => $appointment->patient_name,
                    'field_2' => $appointment->doctor->name_translated,
                    'field_3' => Carbon::createFromFormat(
                        "Y-m-d H:i",
                        $appointment->appointment_date . " " . $appointment->appointment_time
                    )->format("d M h:i A"),
                    'template_language' => 'en_US'
                ]
            );
        } else {
            $service = new \App\Services\MSG91Service;
            $service->sendSMS("67496b2bd6fc053936007493", $data, false);
        }
    }

    public function sendWARescheduleMessage($appointment)
    {
        $client = Client::find($appointment->client_id);
        $data = [
            [
                "mobiles" => $appointment->booking_whatsapp_number,
                "patientname" => $appointment->patient_name,
                "doctorname" => $appointment->doctor->name_translated,
                "client_id" => $appointment->client_id,
                "appointmentdate" => Carbon::createFromFormat(
                    "Y-m-d H:i",
                    $appointment->appointment_date . " " . $appointment->appointment_time
                )->format("d M h:i A")
            ]
        ];

        if ($client->notification_mode === 'whatsapp') {
            $waService = new WAConnectService();
            $waService->sendTemplateMessage(
                $client,
                $appointment->booking_whatsapp_number,
                'send_reschedule_reminder',
                [
                    'field_1' => $appointment->patient_name,
                    'field_2' => $appointment->doctor->name_translated,
                    'field_3' => Carbon::createFromFormat(
                        "Y-m-d H:i",
                        $appointment->appointment_date . " " . $appointment->appointment_time
                    )->format("d M h:i A"),
                    'template_language' => 'en_US'
                ]
            );
        } else {
            $service = new \App\Services\MSG91Service;
            $service->sendSMS("676e4c64d6fc051a9f3cbea3", $data, false);
        }
    }

    public function cleanPhoneNumber($phone)
    {
        if (substr($phone, 0, 3) == "+91") {
            $phone = substr($phone, 3);
        }
        if (strlen($phone) == 11 && $phone[0] == 0) {
            $phone = substr($phone, 1);
        }
        if (strlen($phone) == 12) {
            return $phone;
        }
        if (strlen($phone) < 12) {
            return "91" . $phone;
        }
    }

    public function createFollowup()
    {
        $appointmentId = request("appointment_id");
        $date = request('date');
        if ($appointmentId != "new") {
            $appointment = WhatsAppAppointment::find($appointmentId);
            if ($appointment == null) {
                return response()->json(["error" => "Appointment not found"], 404);
            }
            $followup = new Followup;
            $followup->name = $appointment->patient_name;
            $followup->mobilenumber = $appointment->booking_whatsapp_number;
            $followup->client_id = $appointment->client_id;
            $followup->doctor_id = $appointment->doctor->id;
            $followup->followup_date = $date;
        } else {
            $date = request('date');
            $followup = new Followup;
            $followup->name = request('patient_name');
            $followup->mobilenumber = $this->cleanPhoneNumber(request('mobile_number'));
            $followup->client_id = auth()->user()->client_id;
            $followup->doctor_id = auth()->user()->id;
            $followup->followup_date = $date;
        }
        $followup->save();
        return $followup;
    }

    public function updatePatientId()
    {
        $appointmentId = request("appointment_id");
        $patientId = request("patient_id");

        $appointment = WhatsAppAppointment::find($appointmentId);
        if ($appointment == null) {
            return response()->json(["error" => "Appointment not found"], 404);
        }

        if ($appointment->client_id != auth()->user()->client_id) {
            return response()->json(["error" => "Unauthorized"], 401);
        }

        $appointment->patient_id = $patientId;
        $appointment->save();

        return response()->json([
            "appointment_id" => $appointment->id,
            "patient_id" => $appointment->patient_id
        ]);
    }

    public function deleteFollowup()
    {
        $id = request('id');
        $followup = Followup::find($id);

        if (!$followup) {
            return response()->json(['error' => 'Followup not found'], 404);
        }

        if ($followup->client_id != auth()->user()->client_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $followup->delete();
        return response()->json(['success' => true]);
    }

    public function setPriority()
    {
        $appointmentId = request("appointment_id");

        $appointment = WhatsAppAppointment::find($appointmentId);
        if ($appointment == null) {
            return response()->json(["error" => "Appointment not found"], 404);
        }

        if ($appointment->client_id != auth()->user()->client_id) {
            return response()->json(["error" => "Unauthorized"], 401);
        }
        if ($appointment->status == "Cancelled")
            $appointment->status = "Waiting";
        $appointment->priority = 1;
        $appointment->save();

        return response()->json([
            "appointment_id" => $appointment->id,
            "priority" => $appointment->priority
        ]);
    }

    /**
     * Create or update a WhatsApp appointment via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCreateOrUpdateAppointment(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'id' => 'nullable|exists:whats_app_appointments,id',
            'patient_name' => 'required|string|max:255',
            'booking_whatsapp_number' => 'required|string|max:20',
            'booking_contact_number' => 'nullable|string|max:20',
            'doctor_id' => 'required|exists:filament_users,id',
            'client_id' => 'required|exists:clients,id',
            'hospital_id' => 'required|exists:hospitals,id',
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i',
            'appointment_end_time' => 'required|date_format:H:i',
            'status' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|in:1,2,3',
            'patient_id' => 'nullable|exists:patients,id',
            'amount' => 'nullable|integer',
            'payment_status' => 'nullable|string|max:50',
            'payment_url' => 'nullable|string',
            'payment_refid' => 'nullable|string',
            'video_conf_url' => 'nullable|string',
        ]);

        // Check if this is an update or create operation
        $id = $request->input('id');
        $isUpdate = !empty($id);

        if ($isUpdate) {
            // Update existing appointment
            $appointment = WhatsAppAppointment::findOrFail($id);
            
            // Check if the user has permission to update this appointment
            if ($appointment->client_id != $this->clientId) {
                return response()->json(['error' => 'Unauthorized to update this appointment'], 403);
            }

            $oldStatus = $appointment->status;
            $appointment->fill($validatedData);
            $appointment->save();

            // Check if status changed to Queued and broadcast event if needed
            if ($appointment->status === 'Queued' && $oldStatus !== 'Queued') {
                event(new \App\Events\QueuedAppointmentEvent(
                    $appointment->hospital_id,
                    $appointment->client_id,
                    $appointment->doctor_id
                ));
            }

            return response()->json([
                'message' => 'Appointment updated successfully',
                'appointment' => $appointment
            ]);
        } else {
            // Create new appointment
            // Generate a UUID for the new appointment
            $validatedData['uuid'] = (string) Str::uuid();
            
            // Set default status if not provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = 'Waiting';
            }

            $appointment = new WhatsAppAppointment();
            $appointment->fill($validatedData);
            $appointment->save();

            // If status is Queued, create token and broadcast event
            if ($appointment->status === 'Queued') {
                $appointment->token = $this->createToken($appointment);
                $appointment->save();
                
                // Broadcast queued appointment event
                event(new \App\Events\QueuedAppointmentEvent(
                    $appointment->hospital_id,
                    $appointment->client_id,
                    $appointment->doctor_id
                ));
            }

            return response()->json([
                'message' => 'Appointment created successfully',
                'appointment' => $appointment
            ], 201);
        }
    }
}
