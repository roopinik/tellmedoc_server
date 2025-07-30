<?php

namespace App\Filament\Resources\SharedDocumentResource\Pages;

use App\Filament\Resources\SharedDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSharedDocument extends EditRecord
{
    protected static string $resource = SharedDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
