<?php

namespace App\Filament\Resources\WebUserResource\Pages;

use App\Filament\Resources\WebUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebUsers extends ListRecords
{
    protected static string $resource = WebUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}