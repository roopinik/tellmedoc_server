<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppAppointment;
use Carbon\Carbon;
use App\Services\MSG91Service;
use App\Services\WAConnectService;

class Send30MinRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send30-min-reminder';

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
                    'send_30mn_reminder',
                    [
                        'field_1' => $recipient['patientname'],
                        'field_2' => $recipient['doctorname'],
                        'field_3' => $recipient['time'],
                        'template_language' => 'en_US'
                    ]
                );
            } else {
                $smsService->sendSMS("67496ab4d6fc053d3f1ef004", [$recipient]);
            }
        }
    }

    public function getReciepents()
    {
        $date = now()->format('Y-m-d');
        $startTime = Carbon::now()->addMinutes(30)->format("H:i");
        $endTime = Carbon::now()->addMinutes(value: 35)->format("H:i");
        $clients = \App\Models\Client::whereNot("reminder_type", 0)->select("id")->get()->pluck("id");
        $appointments = WhatsAppAppointment::where('appointment_date', $date)
            ->whereIn('reminder_type', [2, 3])
            ->whereTime('appointment_time', '>=', $startTime)
            ->whereTime('appointment_time', '<=', $endTime)
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
