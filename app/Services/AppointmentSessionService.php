<?php

namespace App\Services;
use App\Models\Client;

use App\Models\HealthCareUser;
use App\Models\Hospital;
use Illuminate\Support\Facades\App;
use App\Services\WAConnectService;
use \App\Models\WhatsAppAppointment;
use Illuminate\Support\Str;
use YorCreative\UrlShortener\Services\UrlService;
use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon;
class AppointmentSessionService
{
    public $session;
    public $whatsAppNumber;
    public $appointment;
    public $input;
    public $vendorId;

    public $doctor;

    public $client;


    public function log($var)
    {
        var_dump($var);
        die();
    }
    public function createSession($vendorId, $whatsAppNumber, $lang)
    {
        $this->session = "session_$whatsAppNumber";
        $this->whatsAppNumber = $whatsAppNumber;
        $client = Client::where("whatsapp_uuid", $vendorId)->first();
        $hospitalId = Hospital::where('client_id', $client->id)->first()->id;
        $session["appointment"]["hospital_id"] = $hospitalId;
        // Initialize session data
        $sessionData = [
            "expires_at" => now()->addMinutes(10)->timestamp,
            "appointment" => [
                "uuid" => Str::uuid(),
                "client_id" => $client->id,
                "booking_whatsapp_number" => $this->cleanPhoneNumber($whatsAppNumber),
                "language" => $lang,
                "reminder_type" => $client->reminder_type,
                "payment_status" => "NEW_APPOINTMENT",
                "appointment_mode" => "offline",
                "status" => "Waiting",
                "hospital_id" => $hospitalId
            ]
        ];

        Cache::put($this->session, $sessionData, $sessionData["expires_at"]);
        $this->handleRequest($vendorId, $whatsAppNumber, "appointment_created");
    }

    public function addToSession($key, $value)
    {
        $session = Cache::get($this->session);
        $session[$key] = $value;
        $expiresAt = $session["expires_at"];
        Cache::put($this->session, $session, $expiresAt);
    }

    public function getIndexValue($key, $index)
    {
        $session = Cache::get($this->session);
        if (isset($session[$key][$index])) {
            return $session[$key][$index];
        } else {
            return null;
        }
    }

    public function getSessionValue($key)
    {
        $session = Cache::get($this->session);
        if (isset($session[$key])) {
            return $session[$key];
        } else {
            return null;
        }
    }

    public function getSlotType()
    {
        if ($this->client->enable_slot_types != true) {
            return null;
        }
        $session = Cache::get($this->session);
        if (isset($session["slot_type"])) {
            return $session["slot_type"];
        } else {
            return null;
        }
    }

    public function handleRequest($vendorId, $whatsppNumber, $input)
    {
        $this->session = "session_$whatsppNumber";
        $this->whatsAppNumber = $whatsppNumber;
        $this->input = $input;
        $this->vendorId = $vendorId;
        $session = Cache::get($this->session);

        if (strtolower($input) == "cancel") {
            Cache::forget($this->session);
            return $this->sendInteractiveResponse(__("whatsapp.cancel_message"), []);
        }

        if (!$this->isSessionActive()) {
            return;
        }

        if ($this->client == null) {
            $this->client = Client::find($session["appointment"]["client_id"]);
        }
        App::setLocale($session["appointment"]["language"]);
        if (!isset($session["appointment"]["doctor_id"])) {
            if (!is_numeric($input)) {
                return $this->showDoctorsList();
            }
            return $this->saveDoctor();
        }

        if (!isset($session["appointment"]["appointment_date"])) {
            if (!$this->validateAppointmentDate($input)) {
                return $this->showAvailableDates();
            }
            return $this->setAppointmentDate();
        }
        if (!isset($session["appointment"]["slot_type"])) {
            if (!$this->validateSlotType($input)) {
                return $this->showSlotTypes();
            }
            return $this->setSlotType();
        }

        if (!isset($session["appointment"]["appointment_time"])) {
            if (!$this->validateAppointmentTime($input)) {
                return $this->showAvailableTime();
            }
            return $this->setAppointmentTime();
        }

        if (!isset($session["appointment"]["patient_name"])) {
            if (is_numeric($input)) {
                return $this->requestPatientName();
            }
            return $this->setpatientName();
        }
    }

    public function validateSlotType($input)
    {
        if (is_numeric($input)) {
            $input = $this->getIndexValue("slot_categories", ((int) $input) - 1);
            if ($input == null) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function setSlotType()
    {
        if (is_numeric($this->input)) {
            $slotType = $this->getIndexValue("slot_categories", ((int) $this->input) - 1);
            if ($slotType == null) {
                return false;
            }
        }
        $session = Cache::get($this->session);
        $session["appointment"]["slot_type"] = $slotType;
        Cache::put($this->session, $session, $session["expires_at"]);
        return $this->showAvailableTime();
    }

    public function showSlotTypes()
    {
        $session = Cache::get($this->session);
        $timeRanges = HealthCareUser::find($session["appointment"]["doctor_id"])
            ->getAvailableTimeRanges($session["appointment"]["appointment_date"], $session["appointment"]["hospital_id"]);


        $slotCategories = [
            'day start' => [],
            'morning' => [],
            'afternoon' => [],
            'evening' => [],
            'day end' => []
        ];

        foreach ($timeRanges as $timeRange) {
            // Extract start time from the range (format: "10:00 AM - 10:30 AM")
            $startTime = trim(explode('-', $timeRange)[0]);
            $hour = (int) Carbon::createFromFormat('h:i A', $startTime)->format('H');

            // Categorize based on hour
            if ($hour >= 5 && $hour < 9) {
                $slotCategories['day start'][] = $timeRange;
            } elseif ($hour >= 9 && $hour < 13) {
                $slotCategories['morning'][] = $timeRange;
            } elseif ($hour >= 13 && $hour < 17) {
                $slotCategories['afternoon'][] = $timeRange;
            } elseif ($hour >= 17 && $hour < 21) {
                $slotCategories['evening'][] = $timeRange;
            } elseif ($hour >= 22 || $hour <= 0) {
                $slotCategories['day end'][] = $timeRange;
            }
        }


        // Filter out empty categories and get only the category names
        $availableCategories = array_keys(array_filter($slotCategories, function ($slots) {
            return !empty($slots);
        }));

        $this->addToSession("slot_categories", $availableCategories);
        if ($session["appointment"]["language"] == "kn") {
            $knCategories = [
                'day start' => 'ದಿನ ಆರಂಭ',
                'morning' => 'ಬೆಳಿಗ್ಗೆ',
                'afternoon' => 'ಮಧ್ಯಾಹ್ನ',
                'evening' => 'ಸಂಜೆ',
                'day end' => 'ದಿನದ ಅಂತ್ಯ'
            ];

            $availableCategories = array_map(function ($category) use ($knCategories) {
                return $knCategories[$category];
            }, $availableCategories);
        }

        $data = [
            "hospital" => $this->client->name,
        ];

        return $this->sendInteractiveListResponse(__("whatsapp.show_slot_types", $data), $availableCategories);
    }

    public function validateAppointmentDate($input)
    {
        if (is_numeric($input)) {
            $date = $this->getIndexValue("dates", ((int) $input) - 1);
            if ($date == null) {
                return false;
            }
        }
        $session = Cache::get($this->session);
        $doctor = \App\Models\HealthCareUser::find($session["appointment"]["doctor_id"]);
        $dates = $doctor->availableDates("offline", $session["appointment"]["hospital_id"]);
        $date = $this->parseDate($date);
        if (in_array($date, $dates)) {
            return true;
        }
        return false;
    }

    public function parseDate($date)
    {
        $date = ucfirst($date);
        $formats = ['Y-m-d', 'Y/m/d', "M d"];
        $dateObj = null;
        foreach ($formats as $format) {
            try {
                if ($dateObj = Carbon::createFromFormat($format, $date)) {
                    $d = $dateObj->format("Y-m-d");
                    break;
                }
            } catch (\Exception $e) {
                $d = false;
            }
        }
        return $d;
    }
    public function validateAppointmentTime($input)
    {
        $time = $input;
        if (is_numeric($input)) {
            $time = $this->getIndexValue("time_slots", ((int) $this->input) - 1);
            if ($time == null) {
                return false;
            }
        }
        $session = Cache::get($this->session);
        $doctor = \App\Models\HealthCareUser::find($session["appointment"]["doctor_id"]);
        $timeSlots = $doctor->getAvailableTimeRanges($session["appointment"]["appointment_date"], $session["appointment"]["hospital_id"]);
        if ($timeSlots->contains($time)) {
            return true;
        }
        $this->log($timeSlots);
        return false;
    }

    public function parseTime($date)
    {
        $formats = ['h:i A', 'H:i', 'h A'];
        $dateObj = null;
        foreach ($formats as $format) {
            try {
                if ($dateObj = Carbon::createFromFormat($format, $date)) {
                    $d = $dateObj->format("H:i");
                    break;
                }
            } catch (\Exception $e) {
                $d = false;
            }
        }
        return $d;
    }

    public function getDoctorsList()
    {
        $client = Client::where("whatsapp_uuid", $this->vendorId)->first();
        $doctors = \App\Models\HealthCareUser::
            where("client_id", $client->id)
            ->whereHas(
                "roles",
                function ($q) {
                    $q->where('name', 'doctor');
                }
            )->orderBy('first_name')->get();
        return $doctors;
    }

    public function showDoctorsList()
    {
        $doctors = $this->getDoctorsList()->map(
            fn($doctor) =>
            $doctor->name_translated
        )->toArray();

        $data = [
            "hospital" => $this->client->name,
            "receptionist_contact" => $this->client->receptionist_contact,
        ];
        $message = __("whatsapp.send_doctors_list", $data);
        return $this->sendInteractiveListResponse($message, $doctors, "Select Doctor");
    }

    public function saveDoctor()
    {
        $doctors = $this->getDoctorsList();
        $doctorId = $doctors[((int) $this->input) - 1]->id;
        $session = Cache::get($this->session);
        $session["appointment"]["doctor_id"] = $doctorId;
        Cache::put($this->session, $session, $session["expires_at"]);
        return $this->showAvailableDates();
    }

    public function showAvailableDates()
    {
        $session = Cache::get($this->session);
        $doctor = \App\Models\HealthCareUser::find($session["appointment"]["doctor_id"]);
        $hospitalId = $session["appointment"]["hospital_id"];
        $dates = $doctor->availableDates("offline", $hospitalId);
        $this->addToSession("dates", $dates);

        $data = [
            "hospital" => $this->client->name,
            "receptionist_contact" => $this->client->receptionist_contact,
        ];
        // Format dates to dd-mm-yyyy
        $dates = collect($dates)->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->toArray();
        $message = __("whatsapp.send_available_dates", $data);
        return $this->sendInteractiveListResponse($message, $dates);
    }


    public function setAppointmentDate()
    {
        if (is_numeric($this->input)) {
            $date = $this->getIndexValue("dates", ((int) $this->input) - 1);
            if ($date == null) {

                return false;
            }
        }
        $session = Cache::get($this->session);
        $session["appointment"]["appointment_date"] = $this->parseDate($date);
        Cache::put($this->session, $session, $session["expires_at"]);
        return $this->showSlotTypes();
    }


    public function showAvailableTime()
    {

        $session = Cache::get($this->session);
        $slotType = $session["appointment"]["slot_type"];
        $doctor = \App\Models\HealthCareUser::find($session["appointment"]["doctor_id"]);
        $hospitalId = $session["appointment"]["hospital_id"];
        $timeSlots = $doctor->getAvailableTimeRanges($session["appointment"]["appointment_date"], $hospitalId);

        // Filter time slots based on slot type
        $filteredSlots = collect($timeSlots)->filter(function ($timeRange) use ($slotType) {
            $startTime = trim(explode('-', $timeRange)[0]);
            $hour = (int) Carbon::createFromFormat('h:i A', $startTime)->format('H');

            switch ($slotType) {
                case 'day start':
                    return $hour >= 5 && $hour < 9;
                case 'morning':
                    return $hour >= 9 && $hour < 13;
                case 'afternoon':
                    return $hour >= 13 && $hour < 17;
                case 'evening':
                    return $hour >= 17 && $hour < 21;
                case 'day end':
                    return $hour >= 22 || $hour <= 0;
                default:
                    return true;
            }
        })->values();

        $this->addToSession("time_slots", $filteredSlots);

        $data = [
            "hospital" => $this->client->name,
            "receptionist_contact" => $this->client->receptionist_contact,
        ];
        $message = __("whatsapp.send_available_slots", $data);
        return $this->sendInteractiveListResponse($message, $filteredSlots);
    }

    public function parseTimeRange($timeRange)
    {
        // Format: "1) 9:00 AM - 10:00 AM (2)"
        // First remove the number prefix and count suffix
        $timeRange = preg_replace('/^\d+\)\s*/', '', $timeRange); // Remove "1) "
        $timeRange = preg_replace('/\s*\(\d+\)$/', '', $timeRange); // Remove " (2)"

        $times = explode(" - ", $timeRange);
        if (count($times) != 2) {
            return false;
        }
        $startTime = $this->parseTime($times[0]);
        $endTime = $this->parseTime($times[1]);
        return [
            "start_time" => $startTime,
            "end_time" => $endTime
        ];
    }

    public function setAppointmentTime()
    {
        if (is_numeric($this->input)) {
            $timeRange = $this->getIndexValue("time_slots", ((int) $this->input) - 1);
            if ($timeRange == null) {
                return false;
            }
        }
        $parsedTimes = $this->parseTimeRange($timeRange);
        if (!$parsedTimes) {
            return false;
        }
        $session = Cache::get($this->session);
        $session["appointment"]["appointment_time"] = $parsedTimes["start_time"];
        $session["appointment"]["appointment_end_time"] = $parsedTimes["end_time"];
        Cache::put($this->session, $session, $session["expires_at"]);
        return $this->requestPatientName();
    }

    public function requestPatientName()
    {
        $data = [
            "hospital" => $this->client->name
        ];
        $message = __("whatsapp.request_patient_name", $data);
        return $this->sendInteractiveResponse($message, ["Cancel"]);
    }

    public function setpatientName()
    {
        $session = Cache::get($this->session);
        $session["appointment"]["patient_name"] = $this->input;
        $session["appointment"]["payment_status"] = "PAYMENT_COMPLETED";
        Cache::put($this->session, $session, $session["expires_at"]);
        return $this->requestConfirmation();
    }

    public function requestConfirmation()
    {
        date_default_timezone_set("Asia/Calcutta");
        $session = Cache::get($this->session);
        $appointmentData = $session["appointment"];

        // Create new appointment model and save to database
        $appointment = new WhatsAppAppointment();
        $appointment->uuid = $appointmentData["uuid"];
        $appointment->client_id = $appointmentData["client_id"];
        $appointment->booking_whatsapp_number = $appointmentData["booking_whatsapp_number"];
        $appointment->language = $appointmentData["language"];
        $appointment->reminder_type = $appointmentData["reminder_type"];
        $appointment->payment_status = $appointmentData["payment_status"];
        $appointment->appointment_mode = $appointmentData["appointment_mode"];
        $appointment->status = $appointmentData["status"];
        $appointment->doctor_id = $appointmentData["doctor_id"];
        $appointment->appointment_date = $appointmentData["appointment_date"];
        $appointment->appointment_time = $appointmentData["appointment_time"];
        $appointment->appointment_end_time = $appointmentData["appointment_end_time"];
        $appointment->patient_name = $appointmentData["patient_name"];
        $appointment->hospital_id = $appointmentData["hospital_id"];
        $appointment->save();

        $doctor = HealthCareUser::find($appointmentData["doctor_id"]);
        $patientName = $appointmentData["patient_name"];
        $doctorName = $doctor->name_translated;
        $date = $appointmentData["appointment_date"];
        $time = $appointmentData["appointment_time"];
        $time = Carbon::createFromFormat("H:i", $time)->format("h:i A");
        $date = Carbon::createFromFormat("Y-m-d", $date)->format("d-m-Y");

        $message = "Appointment Confirmation\nPatient Name: $patientName\nDoctor Name: $doctorName\nAppointment Date: $date\nAppointment Time: $time\n\nWe look forward to seeing you then! If you need to reschedule, please contact the receptionist at 9992292244.";
        $data = [
            "ref" => "APPREF" . $appointment->id,
            "hospital" => $this->client->name,
            "doctor" => $doctorName,
            "patient" => $patientName,
            "date" => $date,
            "time" => $time,
            "receptionist_contact" => $this->client->receptionist_contact,
            "message" => $message
        ];

        $message = __("whatsapp.confirm_appointment", $data);

        // Clear the session after saving to database
        Cache::forget($this->session);

        return $this->sendInteractiveResponse($message, []);
    }

    public function sendInteractiveResponse($message, $replyButtons = [])
    {
        if (env('WHATSAPP_DISABLED') == true)
            $this->log($message);
        $waService = App::make(WAConnectService::class);
        $client = Client::where("whatsapp_uuid", $this->vendorId)->first();
        $waService->sendInteractiveMessage($client, $this->whatsAppNumber, $message, $replyButtons);
        return $message;
    }

    public function sendInteractiveListResponse($message, $listOptions = [], $buttonText = "Select Option")
    {
        if (env('WHATSAPP_DISABLED') == true)
            $this->log($message);

        $waService = App::make(WAConnectService::class);
        $client = Client::where("whatsapp_uuid", $this->vendorId)->first();

        // Format list options into the required structure
        $listData = [
            "button_text" => $buttonText,
            "sections" => [
                [
                    "title" => "Available Options",
                    "rows" => collect($listOptions)->map(function ($option, $index) {
                        return [
                            "row_id" => $index + 1,
                            "title" => ucfirst($option),
                            "description" => ""
                        ];
                    })->all()
                ]
            ]
        ];
        // $this->log($listData);

        return $waService->sendInteractiveListMessage($client, $this->whatsAppNumber, $message, $listData);
    }

    public function sendInteractiveNotification($client, $whatsAppNumber, $message, $replyButtons)
    {
        $waService = App::make(WAConnectService::class);
        $waService->sendInteractiveMessage($client, $whatsAppNumber, $message, $replyButtons);
    }

    public function isSessionActive()
    {
        $session = Cache::get($this->session, false);
        if ($session == false)
            return false;
        $timeStamp = $session["expires_at"];
        if (now()->timestamp > $timeStamp)
            return false;
        return true;
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
}