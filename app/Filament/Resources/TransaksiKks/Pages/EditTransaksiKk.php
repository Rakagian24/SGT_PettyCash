<?php

namespace App\Filament\Resources\TransaksiKks\Pages;

use App\Filament\Resources\TransaksiKks\TransaksiKkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiKk extends EditRecord
{
    protected static string $resource = TransaksiKkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
