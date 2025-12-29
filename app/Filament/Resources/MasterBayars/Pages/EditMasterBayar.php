<?php

namespace App\Filament\Resources\MasterBayars\Pages;

use App\Filament\Resources\MasterBayars\MasterBayarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterBayar extends EditRecord
{
    protected static string $resource = MasterBayarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
