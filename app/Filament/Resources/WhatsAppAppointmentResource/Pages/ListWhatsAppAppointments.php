<?php

namespace App\Filament\Resources\WhatsAppAppointmentResource\Pages;

use App\Filament\Resources\WhatsAppAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListWhatsAppAppointments extends ListRecords
{
    protected static string $resource = WhatsAppAppointmentResource::class;
    public function mount(): void
    {
        if (request()->session()->has('success')) {
            Notification::make()
                ->title(request()->session()->get('success'))
                ->success()
                ->send();
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
