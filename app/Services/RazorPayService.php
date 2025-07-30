<?php
namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

class RazorPayService {
    public function createPaymentLink($client,$appointment){
        $url = env("RPAYURL");
        $data = [];
        $data["amount"]=$appointment->amount*100;
        $data["currency"]="INR";
        $data["accept_partial"]=false;
        $data["expire_by"] = strtotime('+1 hour');;
        $data["reference_id"] = "REFAPP".$appointment->id;
        $data["description"] = "Booking for appointment $client->name";
        $data["reminder_enable"] = false;
        $data["callback_url"] = env("APP_URL")."rpay/appointment/callback/";
        $data["callback_method"] = "get";
        $data["customer"] = [];
        $data["customer"]["name"] = $appointment->patient_name;
        $data["customer"]["contact"] = $appointment->booking_whatsapp_number;
        $data["customer"]["email"] = $appointment->email;
        $data["notify"] = [];
        $data["notify"]["email"] = false;
        $data["notify"]["sms"] = false;
        $data["notes"] = [];
        $data["notes"]["appointment_id"]=$appointment->id;
        $guzzle = new Client([
            'auth' => [$client->rp_key_id, $client->rp_secret],
            'headers' => [
                'Content-Type' => 'application/json',
            ]]);
        $response = $guzzle->post(
                $url,
                ['body' => json_encode(
                    $data
                )]
            );
        $resp = $response->getBody()->getContents();
        $r = json_decode($resp);
        $appointment->payment_url = $r->short_url;
        $appointment->payment_status = "PAYLINK_SENT";
        $appointment->payment_refid = $r->reference_id;
        $appointment->rpay_paylink_id = $r->id;
        $appointment->paylink_expires_at = $r->expire_by;
        $appointment->paylink_response = $resp;
        $appointment->save();
        // $this->sendPayLinkWappNotification($client,$appointment->booking_whatsapp_number,$appointment);
        return redirect($appointment->payment_url);
    }

    public function sendPayLinkWappNotification($client, $whatsAppNumber, $appointment)
    {
        App::setLocale($appointment->language);
        $waService = App::make(WAConnectService::class);
        $data = [
            "patient_name"=>$appointment->patient_name,
            "client"=>$client->name,
            "doctor"=>$appointment->doctor->name_translated,
            "time"=>$appointment->appointment_time,
            "date"=>$appointment->appointment_date,
            "payment_url"=>$appointment->payment_url,
            "receptionist_contact"=>$client->receptionist_contact
        ];
        $message = __("whatsapp.send_paylink",$data);
        $waService->sendMessage($client, $whatsAppNumber, $message);
    }
}
