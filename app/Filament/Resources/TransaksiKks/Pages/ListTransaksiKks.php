<?php

namespace App\Filament\Resources\TransaksiKks\Pages;

use App\Filament\Resources\TransaksiKks\TransaksiKkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiKks extends ListRecords
{
    protected static string $resource = TransaksiKkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
