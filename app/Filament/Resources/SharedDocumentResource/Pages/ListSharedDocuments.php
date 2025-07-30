<?php

namespace App\Filament\Resources\SharedDocumentResource\Pages;

use App\Filament\Resources\SharedDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSharedDocuments extends ListRecords
{
    protected static string $resource = SharedDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
