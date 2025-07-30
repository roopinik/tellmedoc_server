<?php

namespace App\Livewire;

use Spatie\LivewireWizard\Components\StepComponent;

class AppointmentInstructions extends StepComponent
{
    public function render()
    {
        return view('livewire.appointment-instructions');
    }
}
