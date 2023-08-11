<?php

namespace App\Filament\Resources\Manager\WorkResource\Pages;

use App\Filament\Resources\Manager\WorkResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\View;
use Illuminate\Contracts\View\View as ViewView;

class ListWorks extends ListRecords
{
  protected static string $resource = WorkResource::class;

  protected function getActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }
}
