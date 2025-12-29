<?php

namespace App\Filament\Resources\PengajuanBudgetResource\Pages;

use App\Filament\Resources\PengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanBudgets extends ListRecords
{
    protected static string $resource = PengajuanBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}