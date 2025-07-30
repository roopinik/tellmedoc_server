<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppAppointment;
use Carbon\Carbon;
use App\Services\MSG91Service;
use App\Services\WAConnectService;

class SendDailyRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-daily-reminder';

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
                    'send_daily_reminder',
                    [
                        'field_1' => $recipient['patientname'],
                        'field_2' => $recipient['doctorname'],
                        'field_3' => $recipient['time'],
                        'template_language' => 'en_US'
                    ]
                );
            } else {
                $smsService->sendSMS("674967c9d6fc050e6f758472", [$recipient]);
            }
        }
    }

    public function getReciepents()
    {
        $date = now()->format('Y-m-d');
        $clients = \App\Models\Client::whereNot("reminder_type", 0)->select("id")->get()->pluck("id");
        $appointments = WhatsAppAppointment::where('appointment_date', $date)
            ->whereIn('reminder_type', [1, 3])
            ->whereIn('client_id', $clients)
            ->whereNot("status", "Cancelled")
            ->where("payment_status", "PAYMENT_COMPLETED")
            ->get();
        $data = [];
        foreach ($appointments as $appointment) {
            $data[] = [
                "mobiles" => $appointment->booking_whatsapp_number,
                "patientname" => $appointment->patient_name,
                "doctorname" => $appointment->doctor->name_translated,
                "client_id" => $appointment->client_id,
                "time" => Carbon::createFromFormat("H:i", $appointment->appointment_time)->format("h:i A")
            ];
        }
        return $data;
    }
}
