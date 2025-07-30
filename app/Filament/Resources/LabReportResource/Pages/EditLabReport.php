<?php

namespace App\Filament\Resources\LabReportResource\Pages;

use App\Filament\Resources\LabReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLabReport extends EditRecord
{
    protected static string $resource = LabReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
