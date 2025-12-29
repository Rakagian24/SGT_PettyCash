<?php

namespace App\Filament\Resources\ProyeksiResource\Pages;

use App\Filament\Resources\ProyeksiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProyeksi extends CreateRecord
{
    protected static string $resource = ProyeksiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tgl_input'] = now();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}