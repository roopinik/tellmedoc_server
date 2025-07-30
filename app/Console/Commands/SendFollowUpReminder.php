<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Followup;
use Carbon\Carbon;
use App\Services\MSG91Service;
use App\Services\WAConnectService;

class SendFollowUpReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-followup-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->getReciepents();
        $smsService = new MSG91Service();
        $waService = new WAConnectService();

        foreach ($data as $recipient) {
            $client = \App\Models\Client::find($recipient['client_id']);

            if ($client->notification_mode === 'whatsapp') {
                $waService->sendTemplateMessage(
                    $client,
                    $recipient['mobiles'],
                    'send_followup_reminder',
                    [
                        'field_1' => $recipient['patientname'],
                        'field_2' => $recipient['doctorname'],
                        'template_language' => 'en_US'
                    ]
                );
            } else {
                $smsService->sendSMS("676e4d01d6fc052eb43c5b92", [$recipient]);
            }
        }
    }

    public function getReciepents()
    {
        $date = now()->subDays(value: 1)->format('Y-m-d');
        $followups = Followup::where('followup_date', $date)
            ->get();
        $data = [];
        foreach ($followups as $followup) {
            $data[] = [
                "mobiles" => $followup->mobilenumber,
                "patientname" => $followup->name,
                "doctorname" => $followup->doctor->name_translated,
                "client_id" => $followup->client_id,
            ];
        }
        return $data;
    }
}
