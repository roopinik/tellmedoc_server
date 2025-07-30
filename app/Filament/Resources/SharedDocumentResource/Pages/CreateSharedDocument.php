<?php

namespace App\Filament\Resources\SharedDocumentResource\Pages;
use App\Filament\Resources\SharedDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\EncryptionService;
use App\Services\WAConnectService;
class CreateSharedDocument extends CreateRecord
{
    protected static string $resource = SharedDocumentResource::class;
    protected function beforeValidate(): void
    {
        \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::class;
        $tempFile = reset($this->data["document_path"]);
        $path = $tempFile->getRealPath();
        $eS = new EncryptionService();
        $key = env("ENC_KEY");
        $contents = file_get_contents($path);
        $encrypted = $eS->my_encrypt($contents, $key);
        file_put_contents($path, $encrypted);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $ws = new WAConnectService();
        $data['whatsapp_number'] = $ws->cleanPhoneNumber($data["whatsapp_number"]);
        return $data;
    }

    protected function afterCreate(): void
    {
       $clientUuid = auth('filament')->user()->client->uuid;
       $filename = str_replace("shared-documents/$clientUuid/","",$this->record->document_path);
       $was = new WAConnectService();
       $was->sendSharedDocument($clientUuid, $filename, $this->record->whatsapp_number);
    }

}
