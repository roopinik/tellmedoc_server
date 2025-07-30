<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\HealthCareUser;
use App\Models\Person;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use Userstamps;
    use SoftDeletes;
    use HasClient;
    use HasFactory;

    protected $hidden = [
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];

    protected $appends = ["latest_checkin"];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(HealthCareUser::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }


    public function appointmentCheckins(): HasMany
    {
        return $this->hasMany(AppointmentCheckin::class, "appointment_id", "id");
    }



    public function getLatestCheckinAttribute()
    {
        return $this->appointmentCheckins()->latest()->first();
    }

}
