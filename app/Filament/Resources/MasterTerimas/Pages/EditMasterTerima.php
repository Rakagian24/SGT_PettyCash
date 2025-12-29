<?php

namespace App\Filament\Resources\MasterTerimas\Pages;

use App\Filament\Resources\MasterTerimas\MasterTerimaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterTerima extends EditRecord
{
    protected static string $resource = MasterTerimaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
