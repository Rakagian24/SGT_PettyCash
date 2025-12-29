<?php

namespace App\Filament\Resources\ProyeksiResource\Pages;

use App\Filament\Resources\ProyeksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Exports\LaporanProyeksiExport;
use Maatwebsite\Excel\Facades\Excel;

class ListProyeksi extends ListRecords
{
    protected static string $resource = ProyeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}