<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;

class UserDocument extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use HasClient;
}
