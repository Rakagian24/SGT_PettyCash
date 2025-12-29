<?php

namespace App\Filament\Resources\TransaksiKms\Pages;

use App\Filament\Resources\TransaksiKms\TransaksiKmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiKms extends ListRecords
{
    protected static string $resource = TransaksiKmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
