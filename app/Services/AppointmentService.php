<?php

namespace App\Services;
use App\Models\Client;

use Illuminate\Support\Facades\App;
use App\Services\WAConnectService;
use \App\Models\WhatsAppAppointment;
use Illuminate\Support\Str;
use YorCreative\UrlShortener\Services\UrlService;

class AppointmentService {
    public function createAppointment($vendorId, $whatsAppNumber, $lang){
        $client = Client::where("whatsapp_uuid", $vendorId)->first();
        $appointment = new WhatsAppAppointment;
        $uuid = Str::uuid();
        $appointment->uuid = $uuid;
        $appointment->client_id = $client->id;
        $appointment->booking_whatsapp_number = $this->cleanPhoneNumber($whatsAppNumber);
        $appointment->language = $lang;
        $appointment->payment_status = "NEW_APPOINTMENT";
        $appointment->save();
        $url = env("APP_URL");
        if ($client->domain != null) {
            $url = "https://" . $client->domain;
        }
        $url = $url . "wa/appointment/$client->uuid/$uuid";
        $url =  UrlService::shorten($url)
        ->withExpiration(\Carbon\Carbon::now()->addHour()->timestamp)
        ->withOpenLimit(5)
        ->build();
        $this->sendAppontmentWappNotification($client, $whatsAppNumber, $url, $lang);
        return $url;
    }

    public function sendAppontmentWappNotification($client, $whatsAppNumber, $url, $lang)
    {
        $waService = App::make(WAConnectService::class);
        $data = ["client"=> $client->name,"url"=>$url,"receptionist_contact"=>$client->receptionist_contact];
        $message = __("whatsapp.send_appointment_url",$data);
        $waService->sendMessage($client, $whatsAppNumber, $message);
    }

    public function cleanPhoneNumber($phone){
        if(substr($phone, 0,3) == "+91"){
            $phone = substr($phone, 3);
        }
        if(strlen($phone)==11 && $phone[0] == 0){
            $phone = substr($phone, 1);
        }
        if(strlen($phone)==12){
            return $phone;
        }
        if(strlen($phone)<12){
            return "91".$phone;
        }
    }
}
