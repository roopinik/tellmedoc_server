<?php

namespace App\Filament\Resources\HealthCareUserResource\Pages;

use App\Filament\Resources\HealthCareUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHealthCareUser extends EditRecord
{
    protected static string $resource = HealthCareUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
