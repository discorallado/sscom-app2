<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCotizations extends ListRecords
{
    protected static string $resource = CotizationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
