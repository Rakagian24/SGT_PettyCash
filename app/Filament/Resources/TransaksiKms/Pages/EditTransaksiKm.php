<?php

namespace App\Filament\Resources\TransaksiKms\Pages;

use App\Filament\Resources\TransaksiKms\TransaksiKmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiKm extends EditRecord
{
    protected static string $resource = TransaksiKmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
