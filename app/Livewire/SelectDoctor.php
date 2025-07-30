<?php

namespace App\Livewire;

use Spatie\LivewireWizard\Components\StepComponent;
use App\Models\Client;
use App\Models\HealthCareUser;
use Illuminate\Http\Request;
use App\Models\WhatsAppAppointment;
class SelectDoctor extends StepComponent
{
    public $selectedDoctor;
    public $client;
    public $doctors;
    public $hideOnlineAppointment;
    public $mode;
    public $specializations;

    protected $rules = [
        'client.domain' => 'required',
        'client.uuid' => 'required',
        'client.id' => 'required',
        'client.name' => 'required',
        'client.logo_path'
    ];

    public function mount(){
        if(is_array($this->client))
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.select-doctor');
    }

    public function selectDoctor($doctorId, $mode){
        $this->selectedDoctor = $this->doctors->firstWhere(fn($doctor)=>$doctor->id==$doctorId);
        $this->mode = $mode;
        $this->nextStep();
    }

}
