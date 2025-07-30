<?php

namespace App\Filament\Resources\TechnicianResource\Pages;

use App\Filament\Resources\TechnicianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTechnician extends CreateRecord
{
    protected static string $resource = TechnicianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if(auth()->check())
        {
            $data['client_id'] = auth()->user()->client_id;
            return $data;
        }
    }

    protected function afterCreate(): void
    {
        $user = $this->getRecord();
        $user->syncRoles("technician");
    }
}
