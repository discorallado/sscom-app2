<?php

namespace App\Filament\Resources\Manager\WorkResource\Pages;

use App\Filament\Resources\Manager\WorkResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWork extends ViewRecord
{
    protected static string $resource = WorkResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
