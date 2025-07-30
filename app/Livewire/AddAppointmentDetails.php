<?php

namespace App\Livewire;

use App\Models\WhatsAppAppointment;
use Spatie\LivewireWizard\Components\StepComponent;
use App\Models\HealthCareUser;
use App\Models\Client;
use App\Services\RazorPayService;
use Illuminate\Support\Str;
class AddAppointmentDetails extends StepComponent
{
    public $doctor;
    public $client;
    public $availableTimeSlots = [];
    public $mode;
    public $selectedDate;
    public $selectedTime;
    public $name;
    public $email;
    public $contact;

    public $whatsApp;

    public $showContactInput = false;

    public $appointmentUid;

    public $appointment;

    public $byStaff = false;


    public function mount(){
        if($this->appointment == null)
        $this->byStaff = true;
        $this->doctor = HealthCareUser::find($this->state()->forStep('select-doctor')["selectedDoctor"]["id"]);
        $this->mode = $this->state()->forStep('select-doctor')["mode"];
    }

    public function getAppointment(){
        $client = $this->client;
        $appointment = WhatsAppAppointment::make();
        $uuid = Str::uuid();
        $appointment->uuid = $uuid;
        $appointment->client_id = $client->id;
        $appointment->booking_whatsapp_number = "";
        $appointment->language = "en";
        $appointment->by_staff = true;
        $appointment->appointment_mode = "offline";
        $appointment->payment_status = "PAYMENT_COMPLETED";
        return $appointment;
    }
    public function render()
    {
        return view('livewire.add-appointment-details');
    }

    public function selectDate($date){
        $this->selectedDate = $date;
        $this->availableTimeSlots =  $this->doctor->getAvailableTimeSlots($date,$this->mode);
    }

    public function selectTime($time){
        $this->selectedTime = $time;
    }

    public function submit(){
        if($this->selectedDate==null || $this->selectedTime==null)
        return;
        if($this->appointment == null)
        {
            $this->appointment = $this->getAppointment();
        }
        if($this->appointment->by_staff == true)
        {
            if(empty($this->whatsApp)){
                return;
            }
            $this->appointment->booking_whatsapp_number = $this->whatsApp;
        }
        else{
            $this->appointment->appointment_mode = $this->mode;
            $this->appointment->payment_status = "PAYMENT_INITIATED";
        }
        if($this->mode == "offline")
        {
            $this->appointment->payment_status = "PAYMENT_COMPLETED";
        }
        $this->appointment->patient_name = $this->name;
        $this->appointment->email = $this->email;
        $this->appointment->doctor_id = $this->doctor->id;
        $this->appointment->client_id = $this->client->id;
        $this->appointment->appointment_time = $this->selectedTime;
        $this->appointment->appointment_date = $this->selectedDate;
        $this->appointment->reminder_type = $this->client->reminder_type;
        $this->appointment->amount = $this->doctor->getAppointmentFees($this->mode);
        $this->appointment->save();
        if($this->mode =='online')
        {
            $rpayService = new RazorPayService;
            $rpayService->createPaymentLink($this->client, $this->appointment);
        }
        elseif($this->appointment->by_staff == true){
            return redirect()->to('/admin/whats-app-appointments')->with('success', 'Appointment Created Successfully.');;
        }
        else{
            $this->sendAppointmentConfirmation($this->appointment);
            return redirect()->to('/appointment/confirmation/message');
        }
    }

    public function sendAppointmentConfirmation($appointment)
    {
        \App::setLocale($appointment->language);
        $wcService = new \App\Services\WAConnectService;
        $doctor = HealthCareUser::find($appointment->doctor_id);
        $data = [
            "patient_name" => $appointment->patient_name,
            "doctor" => $doctor->name_translated,
            "date" => $appointment->appointment_date,
            "time" => $appointment->appointment_time,
            "type"=> __($appointment->appointment_mode),
            "url" => "",
            "receptionist_contact" => $doctor->receptionist_whatsapp_number,
            "client" => $appointment->client->name,
        ];
        $msg = __("whatsapp.appointment_confirmation_offline", $data);
        $wcService->sendMessage($appointment->client, $appointment->booking_whatsapp_number, $msg);
        \App::setLocale("en");
        $data = [
            "patient_name" => $appointment->patient_name,
            "doctor" => $doctor->name_translated,
            "date" => $appointment->appointment_date,
            "time" => $appointment->appointment_time,
            "type"=> $appointment->appointment_mode,
            "url" => "",
            "receptionist_contact" => $doctor->receptionist_whatsapp_number,
            "client" => $appointment->client->name,
        ];
        $msg = __("whatsapp.appointment_confirmation_offline", $data);
        $wcService->sendMessage($appointment->client, $doctor->receptionist_whatsapp_number, $msg);
        $wcService->sendMessage($appointment->client, $doctor->whats_app_number, $msg);
    }
}
