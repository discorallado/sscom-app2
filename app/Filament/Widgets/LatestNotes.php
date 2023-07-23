<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\NoteResource;
use App\Filament\Resources\Shop\OrderResource;
use App\Models\Note;
use App\Models\Shop\Order;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Squire\Models\Currency;

class LatestNotes extends BaseWidget
{
  protected int | string | array $columnSpan = 'full';

  protected static ?int $sort = 1;

  public function getDefaultTableRecordsPerPageSelectOption(): int
  {
    return 5;
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
    return NoteResource::getEloquentQuery();
  }

  protected function getTableColumns(): array
  {
    return [
      Tables\Columns\TextColumn::make('title')
        ->weight('bold'),
      Tables\Columns\TextColumn::make('text')
        ->html(),
      Tables\Columns\TextColumn::make('user.name'),
      Tables\Columns\TextColumn::make('created_at')
        ->dateTime(),

    ];
  }
  protected function getTableContentGrid(): ?array
  {
    return [
      'md' => 2,
    ];
  }
  protected function getTableActions(): array
  {
    return [
      Tables\Actions\Action::make('open')
        ->url(fn (Note $record): string => NoteResource::getUrl('index', ['record' => $record])),
    ];
  }
}
