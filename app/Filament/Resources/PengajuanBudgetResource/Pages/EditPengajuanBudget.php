<?php

namespace App\Filament\Resources\PengajuanBudgetResource\Pages;

use App\Filament\Resources\PengajuanBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanBudget extends EditRecord
{
    protected static string $resource = PengajuanBudgetResource::class;

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
        
        // Calculate nominal_pengajuan based on details and kisaran_saldo
        $totalDetail = collect($data['details'] ?? [])->sum('nominal_pengajuan_dtl');
        $kisaranSaldo = floatval($data['kisaran_saldo'] ?? 0);
        
        if ($totalDetail >= $kisaranSaldo) {
            $data['nominal_pengajuan'] = $totalDetail - $kisaranSaldo;
        } else {
            $data['nominal_pengajuan'] = $totalDetail;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}