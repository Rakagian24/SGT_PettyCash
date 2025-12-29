<?php

namespace App\Filament\Resources\ProyeksiResource\Pages;

use App\Filament\Resources\ProyeksiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProyeksi extends EditRecord
{
    protected static string $resource = ProyeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tgl_input'] = now();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}