<?php

namespace App\Filament\Resources\WebUserResource\Pages;

use App\Filament\Resources\WebUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebUser extends EditRecord
{
    protected static string $resource = WebUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}