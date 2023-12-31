<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Manager\WorkResource;
use App\Models\Manager\Work;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Squire\Models\Currency;

class ActiveWorks extends BaseWidget
{
  protected int | string | array $columnSpan = 'full';

  protected static ?int $sort = 1;

  protected static ?string $heading = 'Proyectos activos';


  protected function getTableRecordsPerPageSelectOptions(): array
  {
    return [3, 6];
  }

  public function getDefaultTableRecordsPerPageSelectOption(): int
  {
    return 3;
  }

  protected function getDefaultTableSortColumn(): ?string
  {
    return 'created_at';
  }

  protected function getDefaultTableSortDirection(): ?string
  {
    return 'desc';
  }

  protected function getTableQuery(): Builder
  {
    return WorkResource::getEloquentQuery();
  }

  protected function getTableColumns(): array
  {
    return [
      Tables\Columns\TextColumn::make('title')
        ->weight('bold'),
      Tables\Columns\TextColumn::make('user.name'),
      Tables\Columns\TextColumn::make('created_at')
        ->dateTime(),

    ];
  }
  protected function getTableContentGrid(): ?array
  {
    return [
      'md' => 2,
      'xl' => 3,
    ];
  }
  protected function getTableActions(): array
  {
    return [
      Tables\Actions\Action::make('open')
        ->url(fn (Work $record): string => WorkResource::getUrl('index', ['record' => $record])),
    ];
  }
}
