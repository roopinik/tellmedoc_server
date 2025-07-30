<?php

namespace App\Filament\Resources\DoctorLeaveResource\Pages;

use App\Filament\Resources\DoctorLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDoctorLeaves extends ListRecords
{
    protected static string $resource = DoctorLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
