<?php

namespace App\Filament\Resources\LabReportResource\Pages;

use App\Filament\Resources\LabReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLabReports extends ListRecords
{
    protected static string $resource = LabReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
