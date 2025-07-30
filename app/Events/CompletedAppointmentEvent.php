<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompletedAppointmentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hospitalId;
    public $doctorId;

    /**
     * Create a new event instance.
     *
     * @param int $appointmentId The ID of the appointment that was completed.
     * @param int $hospitalId The ID of the hospital.
     * @param int $doctorId The ID of the doctor.
     */
    public function __construct(int $hospitalId, int $doctorId)
    {
        $this->hospitalId = $hospitalId;
        $this->doctorId = $doctorId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): Channel
    {
        // Use a consistent naming convention for channels
        return new Channel("tv.hospital.{$this->hospitalId}.doctor.{$this->doctorId}");
    }
}