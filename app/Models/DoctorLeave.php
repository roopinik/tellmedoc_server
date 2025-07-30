<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use App\Traits\HasClient;

class DoctorLeave extends Model
{
    use HasFactory;
    use HasFactory;
    use Userstamps;
    use HasClient;

    public function doctor(){
        return $this->belongsTo(HealthCareUser::class);
    }
}
