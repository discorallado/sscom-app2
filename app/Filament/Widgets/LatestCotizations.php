<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Manager\CotizationResource;
use App\Models\Manager\Cotization;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LatestCotizations extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;



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
        return CotizationResource::getEloquentQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('codigo')
          ->size('sm')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('fecha')
          ->date()
          ->extraAttributes(function (?Model $record) {
            $fecha = Carbon::parse($record->fecha);
            $hoy = Carbon::parse(now());
            return $fecha->add((int)$record->validez, 'day') <= $hoy
              ? ['class' => 'text-warning-600']
              : ['class' => 'text-primary-600'];
          })
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('work.title')
          ->words(3)
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('work.customer.name')
          ->words(2)
          ->searchable()
          ->sortable(),
          Tables\Columns\TextColumn::make('total_price')
          ->money('clp')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('payments_sum')
          ->label('Pagos')
          ->placeholder('S/P')
          ->sum('payments', 'abono')
          ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('open')
            ->label('Abrir')
                ->url(fn (Cotization $record): string => CotizationResource::getUrl('edit', ['record' => $record])),
        ];
    }
}
