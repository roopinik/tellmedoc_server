<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class DoctorCard extends Component
{
    public $doctor;
    public $appointment;
    public $hideButton = false;
    public $hideOnlineAppointment = false;
    public function mount($doctor){
    }
    public function render()
    {
        return view('livewire.doctor-card');
    }

    public function getAvailableDays(){
        $odays = collect($this->doctor->appointment_slots)->pluck("weekDay");
        return $odays->unique()->map(fn($t)=>__($t))->join(", ");
    }

    public function getLaunguages(){
        return $this->doctor->languages->pluck("name")->join(", ");
    }

    public function getSpecializations(){
        return $this->doctor->specializations->pluck("name")->join(", ");
    }

    public function selectDoctor(){
        $this->parent->selectDoctor($this->doctor);
    }
}
