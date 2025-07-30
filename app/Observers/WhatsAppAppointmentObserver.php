<?php

namespace App\Observers;

use App\Events\QueuedAppointmentEvent;
use App\Models\WhatsAppAppointment;
use App\Events\CompletedAppointmentEvent; 

class WhatsAppAppointmentObserver
{
    /**
     * Handle the WhatsAppAppointment "created" event.
     *
     * @param  \App\Models\WhatsAppAppointment  $appointment
     * @return void
     */
    public function created(WhatsAppAppointment $appointment)
    {
        if ($appointment->status === 'Queued') {
            event(new QueuedAppointmentEvent(
                $appointment->hospital_id,
                $appointment->doctor_id // Only passing two arguments now
            ));
        }
    }

    /**
     * Handle the WhatsAppAppointment "updated" event.
     *
     * @param  \App\Models\WhatsAppAppointment  $appointment
     * @return void
     */
    public function updated(WhatsAppAppointment $appointment)
    {
        // if ($appointment->status === 'Queued' && $appointment->wasChanged('status')) {
        //     event(new QueuedAppointmentEvent(
        //         $appointment->hospital_id,
        //         $appointment->doctor_id // Only passing two arguments now
        //     ));
        // }
        if ($appointment->wasChanged('status')) {
            if ($appointment->status === 'Queued') {
                event(new QueuedAppointmentEvent(
                    $appointment->hospital_id,
                    $appointment->doctor_id
                ));
            } elseif ($appointment->status === 'Completed') {
                event(new QueuedAppointmentEvent(
                    $appointment->hospital_id,
                    $appointment->doctor_id
                ));
            }
        }

    }

    /**
     * Handle the WhatsAppAppointment "deleted" event.
     *
     * @param  \App\Models\WhatsAppAppointment  $appointment
     * @return void
     */
    public function deleted(WhatsAppAppointment $appointment)
    {
        //
    }

    /**
     * Handle the WhatsAppAppointment "restored" event.
     *
     * @param  \App\Models\WhatsAppAppointment  $appointment
     * @return void
     */
    public function restored(WhatsAppAppointment $appointment)
    {
        //
    }

    /**
     * Handle the WhatsAppAppointment "force deleted" event.
     *
     * @param  \App\Models\WhatsAppAppointment  $appointment
     * @return void
     */
    public function forceDeleted(WhatsAppAppointment $appointment)
    {
        //
    }
} 