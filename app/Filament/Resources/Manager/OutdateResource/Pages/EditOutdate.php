<?php

namespace App\Filament\Resources\Manager\OutdateResource\Pages;

use App\Filament\Resources\Manager\OutdateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutdate extends EditRecord
{
    protected static string $resource = OutdateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
