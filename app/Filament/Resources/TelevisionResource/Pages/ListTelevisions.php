<?php

namespace App\Filament\Resources\TelevisionResource\Pages;

use App\Filament\Resources\TelevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTelevisions extends ListRecords
{
    protected static string $resource = TelevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 