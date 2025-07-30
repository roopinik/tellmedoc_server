<?php

namespace App\Filament\Resources\TelevisionResource\Pages;

use App\Filament\Resources\TelevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTelevision extends CreateRecord
{
    protected static string $resource = TelevisionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if(auth()->check())
        {
            $data['client_id'] = auth()->user()->client_id;
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->getRecord();
        $user->syncRoles("device.television");
    }
} 