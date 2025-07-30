<?php

namespace App\Services;
use GuzzleHttp\Client as GZClient;
use \App\Models\Client;
use \App\Services\EncryptionService;
use Illuminate\Support\Facades\Log;
use YorCreative\UrlShortener\Services\UrlService;
use App\Models\WhatsAppMessageLog;
use Illuminate\Http\Response;

class WAConnectService
{

    private function logWhatsAppMessage($client, $phoneNumber, $message, $response)
    {
        $status = $response->getStatusCode() == Response::HTTP_OK ? 'success' : 'failed';

        WhatsAppMessageLog::create([
            'client_id' => $client->id,
            'phone_number' => $phoneNumber,
            'message_body' => $message,
            'status' => $status
        ]);
    }

    public function sendMessage($client, $whatsAppNumber, $message)
    {
        $url = env("WACONNECT_URL") . $client->whatsapp_uuid . "/contact/send-message";
        $data["phone_number"] = $this->cleanPhoneNumber($whatsAppNumber);
        $data["message_body"] = $message;
        $data["contact"] = [];
        $data["contact"]["first_name"] = $whatsAppNumber;
        $c = new GZClient([
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $client->whatsapp_token
            ]
        ]);
        $response = $c->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();
        Log::info($r);

        $this->logWhatsAppMessage($client, $whatsAppNumber, $message, $response);
    }

    public function sendInteractiveMessage($client, $whatsAppNumber, $message, $replyButtons = [])
    {
        $url = env("WACONNECT_URL") . $client->whatsapp_uuid . "/contact/send-interactive-message";
        $data["phone_number"] = $this->cleanPhoneNumber($whatsAppNumber);
        $data["body_text"] = $message;
        $data["message_body"] = $message;
        $data["body"] = $message;
        $data["contact"] = [];
        $data["contact"]["first_name"] = $whatsAppNumber;
        $data["interaction_message_data"] = [];
        if ($replyButtons != null)
            $data["interaction_message_data"] = ["body_text" => $message, "buttons" => $replyButtons];
        $c = new GZClient([
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $client->whatsapp_token
            ]
        ]);
        $response = $c->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();

        $this->logWhatsAppMessage($client, $whatsAppNumber, $message, $response);
        return $r;
    }

    public function sendInteractiveListMessage($client, $whatsAppNumber, $message, $listData = [])
    {
        $url = env("WACONNECT_URL") . $client->whatsapp_uuid . "/contact/send-interactive-message";
        $data["phone_number"] = $this->cleanPhoneNumber($whatsAppNumber);
        $data["body_text"] = $message;
        $data["message_body"] = $message;
        $data["body"] = $message;
        $data["contact"] = [];
        $data["contact"]["first_name"] = $whatsAppNumber;
        $data["interaction_message_data"] = [];
        if ($listData != null)
            $data["interaction_message_data"] = ["interactive_type" => "list", "body_text" => $message, "list_data" => $listData];
        $c = new GZClient([
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $client->whatsapp_token
            ]
        ]);
        $response = $c->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();

        $this->logWhatsAppMessage($client, $whatsAppNumber, $message, $response);
        return $r;
    }

    public function addHeaderFooterToMessage($client, $message, $ln)
    {
        if ($ln == "en") {
            $message = $client->whatsapp_header . $message . $client->whatsapp_footer;
        } else if ($ln == "kn") {
            $message = $client->whatsapp_header_kn . $message . $client->whatsapp_footer_kn;
        }
        return $message;
    }

    public function appAppointmentInstructions($client, $message, $ln)
    {
        if ($ln == "en") {
            $message = $message . $client->appointment_instructions;
        } else if ($ln == "kn") {
            $message = $message . $client->appointment_instructions_kn;
        }
        return $message;
    }

    public function sendTemplateMessage($client, $whatsAppNumber, $template, $config = [])
    {
        $url = env("WACONNECT_URL") . $client->whatsapp_uuid . "/contact/send-template-message";
        $data["phone_number"] = $this->cleanPhoneNumber($whatsAppNumber);
        $data["template_name"] = $template;
        $data["contact"] = [];
        $data["contact"]["first_name"] = $whatsAppNumber;
        $data = array_merge($data, $config);
        $client = new GZClient([
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $client->whatsapp_token
            ]
        ]);
        $response = $client->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );
        $r = $response->getBody()->getContents();
        Log::info($r);

        $this->logWhatsAppMessage($client, $whatsAppNumber, $template, $response);
        return $r;
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

    public function sendSharedDocument($clientUuid, $fileName, $whatsAppNumber)
    {
        $client = Client::where("uuid", $clientUuid)->first();
        $es = new EncryptionService;
        $key = env("ENC_KEY");
        $fileName = $es->my_encrypt($fileName, $key);
        $fileName = base64_encode($fileName);
        $b64ClientUuid = base64_encode($clientUuid);
        $url = env("APP_URL") . "enc/wa/" . "${b64ClientUuid}/$fileName";
        $length = 7;
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $password = substr(str_shuffle($chars), 0, $length);
        $url = UrlService::shorten($url)
            ->withExpiration(\Carbon\Carbon::now()->addDay()->timestamp)
            ->withOpenLimit(5)
            ->withPassword($password)
            ->build();
        $this->sendTemplateMessage($client, $whatsAppNumber, "send_user_document", ["field_1" => $password, "field_2" => $url, "template_language" => "en_US"]);
    }
}
