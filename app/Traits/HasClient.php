<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait HasClient {

    protected static function bootHasCLient()
    {
        static::addGlobalScope('client', function (Builder $query) {
            if (auth()->check()) {
                $query->where('client_id', auth()->user()->client_id);
            }
        });
    }
}