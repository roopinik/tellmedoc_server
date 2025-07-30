<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use App\Traits\HasClient;

class Followup extends Model
{
    use Userstamps;
    use HasFactory;
    use HasClient;

    public function doctor()
    {
        return $this->belongsTo(HealthCareUser::class);
    }
}
