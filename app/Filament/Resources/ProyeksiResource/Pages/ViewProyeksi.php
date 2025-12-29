<?php

namespace App\Filament\Resources\ProyeksiResource\Pages;

use App\Filament\Resources\ProyeksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProyeksi extends ViewRecord
{
    protected static string $resource = ProyeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}