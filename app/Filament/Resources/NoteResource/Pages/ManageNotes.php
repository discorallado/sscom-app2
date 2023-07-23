<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotes extends ManageRecords
{
    protected static string $resource = NoteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = auth()->id();

                return $data;
            }),
        ];
    }
}
