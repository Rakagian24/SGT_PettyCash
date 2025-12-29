<?php

namespace App\Filament\Resources\MasterKlasifikasis\Pages;

use App\Filament\Resources\MasterKlasifikasis\MasterKlasifikasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterKlasifikasi extends EditRecord
{
    protected static string $resource = MasterKlasifikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
