<?php

namespace App\Filament\Resources\Manager\WorkResource\Widgets;

use App\Filament\Resources\Manager\CotizationResource;
use App\Models\Manager\Cotization;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RelatedCotization extends BaseWidget
{


  protected int | string | array $columnSpan = 1;

  protected function getTableQuery(): Builder
  {
    // ...
    $record = request()?->segment(count(request()?->segments()) - 1);
    return CotizationResource::getEloquentQuery()->where('manager_work_id', '=', $record);
  }

  protected function getTableColumns(): array
  {
    return [
      // ...
      Tables\Columns\TextColumn::make('fecha')
        ->date(),
      Tables\Columns\TextColumn::make('codigo'),
      Tables\Columns\TextColumn::make('total_price')
        ->money('clp'),
    ];
  }

  protected function getTableActions(): array
  {
    return [
      Tables\Actions\Action::make('abrir'),
    ];
  }

  protected function getTableBulkActions(): array
  {
    return [];
  }

  protected function isTablePaginationEnabled(): bool
  {
    return false;
  }
}
