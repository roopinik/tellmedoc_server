<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Msg91Log extends Model
{
    protected $fillable = [
        'client_id',
        'message',
        'phone_number'
    ];

    protected $casts = [
        'recipients' => 'array'
    ];
}
