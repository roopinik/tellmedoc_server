<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = DoctorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->check()) {
            // dd($data);
            $data['client_id'] = auth()->user()->client_id;
            return $data;
        }
    }

    protected function afterCreate(): void
    {
        $user = $this->getRecord();
        $user->syncRoles("doctor");
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
