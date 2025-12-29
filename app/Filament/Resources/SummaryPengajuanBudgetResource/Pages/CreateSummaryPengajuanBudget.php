<?php

namespace App\Filament\Resources\SummaryPengajuanBudgetResource\Pages;

use App\Filament\Resources\SummaryPengajuanBudgetResource;
use App\Models\SummaryPengajuanBudget;
use Filament\Resources\Pages\CreateRecord;

class CreateSummaryPengajuanBudget extends CreateRecord
{
    protected static string $resource = SummaryPengajuanBudgetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tgl_input'] = now();
        
        // Auto-populate summary data if not already set
        if (empty($data['kgs']) && empty($data['ogs']) && empty($data['pgs']) && empty($data['bgs'])) {
            $summaryData = SummaryPengajuanBudget::getSummaryData($data['tgl_dari'], $data['tgl_sampai']);
            $data = array_merge($data, $summaryData);
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}