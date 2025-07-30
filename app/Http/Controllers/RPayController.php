<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;


use Illuminate\Http\Request;
use App\Models\WhatsAppAppointment;
use App\Models\HealthCareUser;
use App\Services\WAConnectService;
use Illuminate\Support\Facades\Log;

class RPayController extends Controller
{
    public function callback()
    {
        $paymentId = request("razorpay_payment_id");
        $razorpayPaymentLinkId = request("razorpay_payment_link_id");
        $razorpayPaymentLinkReferenceId = request("razorpay_payment_link_reference_id");
        $razorpayPaymentLinkStatus = request("razorpay_payment_link_status");
        $razorpaySignature = request("razorpay_signature");
        $appointment = WhatsAppAppointment::where("rpay_paylink_id", $razorpayPaymentLinkId)->first();
        if ($appointment->payment_status == "PAYMENT_COMPLETED")
            return "nothing to do";
        $client = $appointment->client;
        $guzzle = new Client([
            'auth' => [$client->rp_key_id, $client->rp_secret],
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        $response = $guzzle->get(
            env("RPAYMENTURL") . "/$paymentId",
        );
        $r = $response->getBody()->getContents();
        $response = json_decode($r);
        $status = "failed";
        if ($appointment->payment_status == "PAYMENT_COMPLETED") {
            return view("appointment_confirmation_status");
        }
        if ($response->status == "captured" || $response->status == "authorized") {
            $status = "success";
            $appointment->rpay_payment_id = $paymentId;
            $appointment->payment_status = "PAYMENT_COMPLETED";
            $appointment->save();
            $this->sendAppointmentConfirmation($appointment);
        }
        return view("appointment_confirmation_status");
    }

    public function webHook()
    {
        $data = request()->all();
        $key = env('WEBHOOKSEC');
        $signature = request()->header('X-Razorpay-Signature');
        $calculatedSig = hash_hmac('sha256', json_encode($data, true), $key);
        $payment = $data["payload"]["payment"]["entity"];
        $email = $payment["email"];
        $contact = $payment["contact"];
        if ($calculatedSig != $signature) {
            abort(response()->json('Unauthorized', 401));
            return;
        }
        $status = $payment["status"];
        if ($status == "captured" || $status == "authorized") {
            $appointmentId = $payment["notes"]["appointment_id"];
            $appointment = WhatsAppAppointment::find($appointmentId);
            if ($appointment->payment_status == "PAYMENT_COMPLETED") {
                return response()->json(["status" => "completed"]);
            }
            $appointment->rpay_payment_id = $payment["id"];
            $appointment->payment_response = json_encode($payment);
            $appointment->payment_status = "PAYMENT_COMPLETED";
            $appointment->save();
            $this->sendAppointmentConfirmation($appointment);
        }
        Log::debug($data);
    }

    public function sendAppointmentConfirmation($appointment)
    {
        App::setLocale($appointment->language);
        $wcService = new WAConnectService;
        $doctor = HealthCareUser::find($appointment->doctor_id);
        $data = [
            "patient_name" => $appointment->patient_name,
            "doctor" => $doctor->name_translated,
            "date" => $appointment->appointment_date,
            "time" => $appointment->appointment_time,
            "type"=> $appointment->appointment_mode,
            "url" => env("JITSIMEETURL") . "appointment" . $appointment->id . "#config.disableDeepLinking=true",
            "receptionist_contact" => $doctor->receptionist_whatsapp_number,
            "client" => $appointment->client->name,
        ];
        $msg = __("whatsapp.appointment_confirmation_online", $data);
        $wcService->sendMessage($appointment->client, $appointment->booking_whatsapp_number, $msg);
        App::setLocale("en");
        $data = [
            "patient_name" => $appointment->patient_name,
            "doctor" => $doctor->name_translated,
            "date" => $appointment->appointment_date,
            "time" => $appointment->appointment_time,
            "type"=> $appointment->appointment_mode,
            "url" => env("JITSIMEETURL") . "appointment" . $appointment->id . "#config.disableDeepLinking=true",
            "receptionist_contact" => $doctor->receptionist_whatsapp_number,
            "client" => $appointment->client->name,
        ];
        $msg = __("whatsapp.appointment_confirmation_online", $data);
        $wcService->sendMessage($appointment->client, $doctor->receptionist_whatsapp_number, $msg);
        $wcService->sendMessage($appointment->client, $doctor->whats_app_number, $msg);
    }
}
