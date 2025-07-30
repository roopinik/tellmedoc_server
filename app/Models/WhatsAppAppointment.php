<?php

namespace App\Models;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppAppointment extends Model
{
    use Userstamps;
    use SoftDeletes;
    use HasClient;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_name',
        'booking_whatsapp_number',
        'booking_contact_number',
        'payment_url',
        'payment_status',
        'payment_refid',
        'video_conf_url',
        'amount',
        'doctor_id',
        'client_id',
        'hospital_id',
        'status',
        'token',
        'appointment_date',
        'appointment_time',
        'appointment_end_time',
        'priority',
        'patient_id',
        'uuid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(HealthCareUser::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeStats(Builder $query)
    {
        $user = auth('filament')->user();
        if ($user->hasRole('doctor'))
            $query->where("doctor_id", $user->id);
    }
}


