<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use App\Services\LiveUpdatesService;
class CreateClient extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = Str::uuid();
        return $data;
    }

    protected function afterCreate(): void
    {
        $service = new LiveUpdatesService;
        $model = $this->record;
        $name = "live_stream_" . $model->id;
        $service->createCollection($name);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
