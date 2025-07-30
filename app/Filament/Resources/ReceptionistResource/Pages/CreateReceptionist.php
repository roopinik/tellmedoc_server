<?php

namespace App\Filament\Resources\ReceptionistResource\Pages;

use App\Filament\Resources\ReceptionistResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReceptionist extends CreateRecord
{
    protected static string $resource = ReceptionistResource::class;

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
        $user->syncRoles("receptionist");
    }
    
}
