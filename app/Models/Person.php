<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;

class Person extends Model
{
    protected $table = 'person';
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use HasClient;
    protected $hidden = [
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];
}
