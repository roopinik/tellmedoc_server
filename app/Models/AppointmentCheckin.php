<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentCheckin extends Model
{
    use HasFactory;
    use Userstamps;
    use HasClient;
    protected $hidden = [
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];
    
    public function appointment():BelongsTo{
        return $this->belongsTo(Appointment::class);
    }

}
