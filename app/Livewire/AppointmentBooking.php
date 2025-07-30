<?php
namespace App\Livewire;

use Spatie\LivewireWizard\Components\WizardComponent;
use App\Livewire\SelectDoctor;
use App\Livewire\AddAppointmentDetails;
use App\Livewire\AppointmentInstructions;
use App\Models\Client;
use App\Models\WhatsAppAppointment;
use Illuminate\Http\Request;
use App\Traits\WizardQuerySteps;
use View;
use Livewire\Attributes\On;
class AppointmentBooking extends WizardComponent
{

    use WizardQuerySteps;

    public $clientuuid;
    public $client;

    public $doctors;
    public $appointment;

    public $hideOnlineAppointment = false;
    public $specializations;
    public $appointmentUid;

    protected $queryString = [
        'currentStepName'
    ];



    public function mount(){
        $locale = session('td_locale',"en");
        $this->clientuuid = \Route::current()->parameter('clientuid');
        $this->client = Client::where("uuid", $this->clientuuid)->first();
        $this->doctors = \App\Models\HealthCareUser::with("roles")->with("specializations")->whereHas("roles", function($q) {
            $q->where("name", "doctor");
        })->where("client_id", $this->client->id)->get();
        $this->appointmentUid = \Route::current()->parameter('appointmentuid');
        $this->appointment = $this->getAppointment();
        if(request("l",null)==null)
        if($this->appointment)
        app()->setLocale($this->appointment->language);
        $this->specializations = $this->client->specializations;
        $this->setStepState("select-doctor",["specializations"=>$this->specializations,"client"=>$this->client,"doctors"=>$this->doctors,"hideOnlineAppointment"=>$this->hideOnlineAppointment]);
        $this->setStepState("add-details",["client"=>$this->client,"doctors"=>$this->doctors,"appointment"=>$this->appointment]);
        View::share('client', $this->client);
    }

    public function getAppointment(){
        if($this->appointmentUid!="new")
        {
            $appointment = WhatsAppAppointment::where("uuid",$this->appointmentUid)->first();
            if($appointment == null||$appointment->payment_status == "PAYMENT_COMPLETED")
            return abort(404);
            return $appointment;
        }
        if(auth('filament')->user()==null||!auth('filament')->user()->can('create.internal.appointment'))
        return abort(403);
        $this->hideOnlineAppointment = true;
        return null;
    }

    #[On('locale-changed')]
    public function toggleLocale(){
        $l = app()->getLocale();
        dd("hii");
        if($l=="en"){
            $locale = "kn";
        }
        else{
            $locale = "en";
        }
        app()->setLocale($locale);
        return redirect(request()->header('Referer')."?l=$locale");
    }


    public function steps() : array
    {
        return [
            SelectDoctor::class,
            AddAppointmentDetails::class,
            AppointmentInstructions::class,
        ];
    }

    public function render()
    {
        $currentStepState = $this->getCurrentStepState();
        $client = $this->client;
        return view('livewire-wizard::wizard', compact('currentStepState',"client"))->layout("components.layouts.appointment");
    }

}
