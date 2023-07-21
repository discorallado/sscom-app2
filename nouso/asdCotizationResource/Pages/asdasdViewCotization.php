<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCotization extends ViewRecord
{
    protected static string $resource = CotizationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
