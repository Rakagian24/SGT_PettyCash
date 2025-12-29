<?php

namespace App\Filament\Resources\MasterTerimas\Pages;

use App\Filament\Resources\MasterTerimas\MasterTerimaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterTerimas extends ListRecords
{
    protected static string $resource = MasterTerimaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
