<?php

namespace App\Filament\Resources\MasterBayars\Pages;

use App\Filament\Resources\MasterBayars\MasterBayarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterBayars extends ListRecords
{
    protected static string $resource = MasterBayarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
