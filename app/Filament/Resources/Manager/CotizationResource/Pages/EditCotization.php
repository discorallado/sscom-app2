<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCotization extends EditRecord
{
  protected static string $resource = CotizationResource::class;

  public function hasCombinedRelationManagerTabsWithForm(): bool
  {
    return true;
  }

  protected function getActions(): array
  {
    return [
      Actions\DeleteAction::make(),
      Actions\RestoreAction::make(),
      Actions\ForceDeleteAction::make(),
    ];
  }
}
