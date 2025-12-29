<?php

namespace App\Filament\Resources\PengajuanBudgetResource\Pages;

use App\Filament\Resources\PengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanBudget extends ViewRecord
{
    protected static string $resource = PengajuanBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}