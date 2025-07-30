<?php


namespace App\Services;

use \App\Models\HealthCareUser;
use \App\Models\Client;
use \App\Models\Hospital;
use \App\Models\WhatsAppAppointment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use \Illuminate\Support\Facades\DB;

class WAFlowService
{
    public function createAppointmentFromWhatsApp($resp, $whatsAppNumber)
    {
        $data = $this->parseFlowresponse($resp);
        $data["whatsappnumber"] = $whatsAppNumber;
        $data["channel"] = "whatsapp";
        $info = $this->createAppointment($data);
        $this->sendConfirmation($info[0], $info[1], $info[2], $info[3], $data["time"]);
        return $data;
    }

    public function createAppointmentFromMobileApp($data)
    {
        $data["time"] = substr($data["time"], 0, -4);
        $range = $this->convertTo24HourFormat($data["time"]);
        $data["startTime"] = $range["startTime"];
        $data["endTime"] = $range["endTime"];
        $data["whatsappnumber"] = $data["mobile"];
        $data["ln"] = "en";
        $data["by_staff"] = true;
        $data["channel"] = "app";
        $info = $this->createAppointment($data);
        return $info[0];
    }

    public function createAppointment($data)
    {
        $doctor = HealthCareUser::find($data["doctor"]);
        if (array_key_exists("healthcareid", $data))
            $hospitalid = $data["healthcareid"];
        else
            $hospitalid = $data["defaulthealthcareid"];

        $hospital = Hospital::find($hospitalid);
        if (!isset($data["is_new"]))
            $data["is_new"] = "yes";
        if ($data["is_new"] == 'no')
            $appointment = WhatsAppAppointment::find($data["id"]);
        else {
            $appointment = new WhatsAppAppointment();
            $uuid = Str::uuid();
            $appointment->uuid = $uuid;
        }
        if (array_key_exists("by_staff", $data)) {
            $appointment->by_staff = true;
        }
        if (array_key_exists("walk_in", $data)) {
            $appointment->walk_in = $data["walk_in"];
        }
        $client = Client::find($doctor->client_id);

        $appointment->client_id = $client->id;
        $appointment->appointment_date = $data["date"];
        $appointment->patient_name = $data["patientname"];
        $appointment->alternate_mobile = $data["mobile"];
        $appointment->appointment_time = $data["startTime"];
        $appointment->appointment_end_time = $data["endTime"];
        $appointment->client_id = $client->id;
        $appointment->doctor_id = $data["doctor"];
        $appointment->channel = $data["channel"];
        $appointment->booking_whatsapp_number = $this->cleanPhoneNumber($data["whatsappnumber"]);
        $appointment->language = "en";
        $appointment->reminder_type = $client->reminder_type;
        $appointment->payment_status = "PAYMENT_COMPLETED";
        $appointment->appointment_mode = "offline";
        $appointment->status = "Waiting";
        $appointment->language = $data["ln"];
        $appointment->hospital_id = $hospitalid;
        App::setLocale($appointment->language);
        if (isset($data["highpriority"]) && $data["highpriority"] == true) {
            $appointment->priority = 1;
        } else {
            if ($appointment->client->priority == 1) {
                $appointment->priority = 3;
            } else {
                $appointment->priority = 2;
            }
        }

        $appointment->save();
        return [$appointment, $doctor, $client, $hospital];
    }

    public function sendConfirmation($appointment, $doctor, $client, $hospital, $timerange)
    {
        $data = [
            "ref" => "APPREF" . $appointment->id,
            "hospital" => $hospital->name,
            "doctor" => $doctor->name_translated,
            "patient" => $appointment->patient_name,
            "date" => date("jS M", strtotime($appointment->appointment_date)),
            "time" => $timerange,
            "receptionist_contact" => $client->receptionist_contact,
        ];
        $mobile = $this->cleanPhoneNumber($appointment->booking_whatsapp_number);
        $data["mobile"] = $mobile;
        $message = __("whatsapp.confirm_appointment", $data);

        $waService = App::make(WAConnectService::class);
        $message = $waService->appAppointmentInstructions($client, $message, $appointment->language);
        $message = $waService->addHeaderFooterToMessage($client, $message, $appointment->language);
        $resp = $waService->sendInteractiveMessage($client, $appointment->booking_whatsapp_number, $message, ["Check Waitlist"]);
    }


    public function parseFlowresponse($resp)
    {
        $response = $resp;
        $response = explode("<br>", $response);
        $json = [];
        foreach ($response as $r) {
            if ($r == "")
                continue;
            $d = explode(": ", $r);
            $json[$d[0]] = $d[1];
        }
        if (!array_key_exists("mobile", $json))
            $json["mobile"] = "";
        $range = $this->convertTo24HourFormat($json["time"]);
        $json["startTime"] = $range["startTime"];
        $json["endTime"] = $range["endTime"];
        return $json;
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

    function convertTo24HourFormat($timeRange)
    {
        // Split the range into start and end times
        [$start, $end] = explode(' - ', $timeRange);

        // Convert times to 24-hour format
        $startTime = date("H:i", strtotime($start));
        $endTime = date("H:i", strtotime($end));

        return ["startTime" => $startTime, "endTime" => $endTime];
    }

    function getFlowAppointment($hospitalId)
    {
        $user = auth()->user();
        $doctorId = $user->id;
        $date = now()->format('Y-m-d');
        $query = "select id,patient_name,
        appointment_time AS start_time,
        appointment_end_time AS end_time ,
        booking_whatsapp_number AS wamobile,
        alternate_mobile AS almobile,
        `status`,
        token,
        doctor_id,
        hospital_id  from whats_app_appointments
        where   appointment_date ='$date'
        and hospital_id = '$hospitalId'
        and doctor_id = '$doctorId'
        and status = 'Queued'
        order by
        appointment_time asc,
        priority asc,
        token asc
        limit 1
        ";
        $record = DB::select($query);
        return $record;
    }
}
