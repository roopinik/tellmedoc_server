<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AppointmentService;
use App\Services\AppointmentSessionService;
use App\Services\WAConnectService;
use App\Services\WAFlowService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppAppointment;

class WhatsAppConnectController extends Controller
{
    public function handleMessage(Request $request, $vendorId)
    {
        $inputs = request()->all();
        $whatsppNumber = $inputs["whatsapp_number"];
        $text = $inputs["action"];
        $client = Client::where("whatsapp_uuid", $vendorId)->first();
        if(isset($inputs["list_row_id"])) {
            $text = $inputs["list_row_id"];
        }
        if (str_contains($text, "defaulthealthcareid")) {
            $waFlowService = new WAFlowService;
            $waFlowService->createAppointmentFromWhatsApp($text, $whatsppNumber);
            return;
        }

        if (strtolower($text) == "form") {
            $waConnectService = new WAConnectService;
            $waConnectService->sendTemplateMessage($client, $whatsppNumber, $client->flow_template_id, ["template_language" => "en"]);
        } else if ($text == "form kannada") {
            $waConnectService = new WAConnectService;
            $waConnectService->sendTemplateMessage($client, $whatsppNumber, $client->flow_template_id_kn, ["template_language" => "kn"]);
        }

        if (strtolower($text) == "check waitlist") {
            $this->handleWaitlistCheck($vendorId, $whatsppNumber);
            return;
        }
        if ($client->message_type == "message") {
            if ($text == "new appointment") {
                $this->createSession($vendorId, $whatsppNumber, "en");
            } else if ($text == "ಹೊಸ ಅಪಾಯಿಂಟ್ಮೆಂಟ್") {
                $this->createSession($vendorId, $whatsppNumber, "kn");
            } else {
                $this->handleSessionRequest($vendorId, $whatsppNumber, $text);
            }
        } else {
            if (strtolower($text) == "new appointment") {
                $waConnectService = new WAConnectService;
                $waConnectService->sendTemplateMessage($client, $whatsppNumber, $client->flow_template_id, ["template_language" => "en"]);
            } else if ($text == "ಹೊಸ ಅಪಾಯಿಂಟ್ಮೆಂಟ್") {
                $waConnectService = new WAConnectService;
                $waConnectService->sendTemplateMessage($client, $whatsppNumber, $client->flow_template_id_kn, ["template_language" => "kn"]);
            }
        }
    }



    public function createSession($vendorid, $whatsppNumber, $lang)
    {
        $appointmentSessionService = new AppointmentSessionService;
        $appointmentSessionService->createSession($vendorid, $whatsppNumber, $lang);
    }

    public function handleSessionRequest($vendorId, $whatsAppNumber, $text)
    {
        $appointmentSessionService = new AppointmentSessionService;
        $appointmentSessionService->handleRequest($vendorId, $whatsAppNumber, $text);
    }


    public function handleWaitlistCheck($vendorId, $whatsppNumber)
    {
        $client = Client::where("whatsapp_uuid", $vendorId)->first();
        if (!$client) {
            return;
        }
        // First get the user's appointment to get the doctor_id
        $userAppointment = WhatsAppAppointment::where("client_id", $client->id)
            ->where(function ($query) use ($whatsppNumber) {
                $query->where("booking_whatsapp_number", $whatsppNumber)
                    ->orWhere("alternate_mobile", $whatsppNumber);
            })
            ->where("status", "Queued")
            ->where("appointment_date", date("Y-m-d"))
            ->first();

        if (!$userAppointment) {
            $message = "You don't have any active appointments in queue today.";
            $waConnectService = new WAConnectService;
            $waConnectService->sendMessage($client, $whatsppNumber, $message);
            return;
        }
        // Get all queued appointments ordered by time, priority and token
        $queue = WhatsAppAppointment::where("client_id", $client->id)
            ->where("status", "Queued")
            ->where("payment_status", "PAYMENT_COMPLETED")
            ->where("appointment_date", date("Y-m-d"))
            ->where("doctor_id", $userAppointment->doctor_id)
            ->orderBy("appointment_time", "asc")
            ->orderBy("priority", "asc")
            ->orderBy("token", "asc")
            ->get();
        $totalInQueue = $queue->count();
        $userPosition = 0;
        // Find position of user's token in the queue
        foreach ($queue as $index => $appointment) {
            if ($appointment->id === $userAppointment->id) {
                $userPosition = $index + 1;
                break;
            }
        }
        $message = "Total members in queue for Dr. " . $userAppointment->doctor->name_translated['en'] . ": $totalInQueue\n";
        if ($userPosition > 0) {
            $message .= "Your waitlist number: $userPosition";
        } else {
            $message .= "Your appointment is not in queue.";
        }
        $waConnectService = new WAConnectService;
        $waConnectService->sendMessage($client, $whatsppNumber, $message);
    }
}