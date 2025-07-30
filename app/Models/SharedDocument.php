<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use App\Traits\HasClient;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedDocument extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use HasClient;

    public function client(){
        return $this->belongsTo(Client::class);
    }
}
