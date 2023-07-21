<?php

namespace App\Filament\Resources\Manager\OutdateResource\Pages;

use App\Filament\Resources\Manager\OutdateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutdates extends ListRecords
{
    protected static string $resource = OutdateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
