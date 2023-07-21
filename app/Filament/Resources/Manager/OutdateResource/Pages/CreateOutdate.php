<?php

namespace App\Filament\Resources\Manager\OutdateResource\Pages;

use App\Filament\Resources\Manager\OutdateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOutdate extends CreateRecord
{
    protected static string $resource = OutdateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
