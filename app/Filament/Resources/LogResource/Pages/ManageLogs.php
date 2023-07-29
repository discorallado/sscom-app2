<?php

namespace App\Filament\Resources\LogResource\Pages;

use App\Filament\Resources\LogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLogs extends ManageRecords
{
  protected static string $resource = LogResource::class;

  protected function getActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }
  protected function getHeaderWidgets(): array
  {
    return LogResource::getWidgets();
  }
}
