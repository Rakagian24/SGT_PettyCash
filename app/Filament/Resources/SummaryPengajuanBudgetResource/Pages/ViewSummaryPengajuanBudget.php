<?php

namespace App\Filament\Resources\SummaryPengajuanBudgetResource\Pages;

use App\Filament\Resources\SummaryPengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSummaryPengajuanBudget extends ViewRecord
{
    protected static string $resource = SummaryPengajuanBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}