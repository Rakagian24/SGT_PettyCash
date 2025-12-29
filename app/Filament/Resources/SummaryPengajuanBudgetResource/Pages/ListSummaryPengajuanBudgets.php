<?php

namespace App\Filament\Resources\SummaryPengajuanBudgetResource\Pages;

use App\Filament\Resources\SummaryPengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSummaryPengajuanBudgets extends ListRecords
{
    protected static string $resource = SummaryPengajuanBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}