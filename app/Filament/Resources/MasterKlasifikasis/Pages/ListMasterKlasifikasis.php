<?php

namespace App\Filament\Resources\MasterKlasifikasis\Pages;

use App\Filament\Resources\MasterKlasifikasis\MasterKlasifikasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterKlasifikasis extends ListRecords
{
    protected static string $resource = MasterKlasifikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
