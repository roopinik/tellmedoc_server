<?php

namespace App\Filament\Resources\WhatsAppAppointmentResource\Pages;

use App\Filament\Resources\WhatsAppAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsAppAppointment extends EditRecord
{
    protected static string $resource = WhatsAppAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
