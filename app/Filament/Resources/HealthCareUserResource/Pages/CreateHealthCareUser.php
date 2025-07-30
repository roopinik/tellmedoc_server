<?php

namespace App\Filament\Resources\HealthCareUserResource\Pages;

use App\Filament\Resources\HealthCareUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHealthCareUser extends CreateRecord
{
    protected static string $resource = HealthCareUserResource::class;
    protected function afterCreate(): void
    {
        $user = $this->getRecord();
        $user->syncRoles("healthcare.admin");
    }
}
