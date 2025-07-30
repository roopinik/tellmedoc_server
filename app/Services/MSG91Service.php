<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Msg91Log;

class MSG91Service
{
    public function sendSMS($templateId, $recipients, $debug = true)
    {
        $data = [];
        $data["template_id"] = $templateId;
        $data["short_url"] = 0;
        $data["realTimeResponse"] = 1;
        $url = env('MSG91_URL');
        $guzzle = new Client([
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json',
                'authkey' => env('MSG91_AUTH_KEY')
            ]
        ]);
        // Remove client_id from recipients data while preserving original $recipients
        $requestRecipients = array_map(function ($recipient) {
            $recipientCopy = $recipient;
            unset($recipientCopy['client_id']);
            return $recipientCopy;
        }, $recipients);
        $data["recipients"] = $requestRecipients;
        $response = $guzzle->post(
            $url,
            [
                'body' => json_encode(
                    $data
                )
            ]
        );

        $r = $response->getBody()->getContents();
        $response = json_decode($r);

        // Log each message for each recipient
        foreach ($recipients as $recipient) {
            if (isset($recipient['client_id']) && isset($recipient['mobiles'])) {
                Msg91Log::create([
                    'client_id' => $recipient['client_id'],
                    'message' => json_encode($data),
                    'phone_number' => $recipient['mobiles']
                ]);
            }
        }
    }
}
