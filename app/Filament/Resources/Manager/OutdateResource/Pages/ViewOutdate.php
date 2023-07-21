<?php

namespace App\Filament\Resources\Manager\OutdateResource\Pages;

use App\Filament\Resources\Manager\OutdateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOutdate extends ViewRecord
{
    protected static string $resource = OutdateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
