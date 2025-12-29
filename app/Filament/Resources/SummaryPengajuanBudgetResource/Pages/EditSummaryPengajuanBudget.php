<?php

namespace App\Filament\Resources\SummaryPengajuanBudgetResource\Pages;

use App\Filament\Resources\SummaryPengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSummaryPengajuanBudget extends EditRecord
{
    protected static string $resource = SummaryPengajuanBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tgl_input'] = now();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}