<?php

namespace App\Observers;
use Illuminate\Database\Eloquent\Model;

class ClientRecordObserver
{
    public function creating(Model $record)
    {
        if (auth()->check()) {
            $record->client_id = auth()->user()->client_id;
        }
    }
}
