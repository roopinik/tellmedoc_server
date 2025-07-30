<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// No need for PresenceChannel or PrivateChannel unless you're explicitly using them
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuedAppointmentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hospitalId; // Make public for easier access in broadcastWith, or keep private and use getters
    public $doctorId;   // Make public for easier access in broadcastWith

    /**
     * Create a new event instance.
     *
     * @return void
     */
    // Remove $clientId from here if you're not passing it from the observer
    public function __construct($hospitalId, $doctorId) // Removed $clientId
    {
        $this->hospitalId = $hospitalId;
        $this->doctorId = $doctorId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // CORRECT: Combine hospitalId and doctorId into a single channel name string
        // Example: 'tv.hospital.123.doctor.456'
        return new Channel('tv.hospital.' . $this->hospitalId . '.doctor.' . $this->doctorId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'queued-appointment-update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'action' => 'appointments_updated',
            'doctor_id' => (string) $this->doctorId,
            'hospital_id' => $this->hospitalId,
            // If client_id is needed, ensure it's passed into the constructor first
            // 'client_id' => $this->clientId
        ];
    }
}