<?php

namespace App\Filament\Resources\TelevisionResource\Pages;

use App\Filament\Resources\TelevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTelevision extends EditRecord
{
    protected static string $resource = TelevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 