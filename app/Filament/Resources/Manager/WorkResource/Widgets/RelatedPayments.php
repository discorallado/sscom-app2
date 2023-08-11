<?php

namespace App\Filament\Resources\Manager\WorkResource\Widgets;

use App\Filament\Resources\Manager\PaymentResource;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class RelatedPayments extends BaseWidget
{

  public array $data_list = [
    'calc_columns' => [
      'total_price' => 'money_clp',
      //   'abono',
      //   'saldo',
    ],
    'calc_columns_format' => [
      'money_clp',
      //   'money_clp',
      //   'money_clp',
    ],
  ];

  protected int | string | array $columnSpan = 'full';

  protected function getTableQuery(): Builder
  {
    // ...
    $record = request()?->segment(count(request()?->segments()) - 1);
    return PaymentResource::getEloquentQuery()->where('manager_work_id', '=', $record);
  }

  protected function getTableColumns(): array
  {
    return [
      // ...
      Tables\Columns\TextColumn::make('fecha')
        ->date(),
      Tables\Columns\BadgeColumn::make('cotization.codigo')
        ->placeholder('Sin cotizacion')
        ->colors(['primary', '' => null]),

      Tables\Columns\BadgeColumn::make('bill.doc')
        ->placeholder('Sin factura'),

      Tables\Columns\TextColumn::make('total_price')
        ->money('clp'),
      Tables\Columns\TextColumn::make('abono')
        ->money('clp'),
      Tables\Columns\TextColumn::make('saldo')
        ->money('clp'),
    ];
  }

  protected function getTableActions(): array
  {
    return [
      Tables\Actions\Action::make('abrir'),
    ];
  }

  protected function isTablePaginationEnabled(): bool
  {
    return false;
  }

  protected function getTableContentFooter(): ?View
  {
    return view('table.footer', $this->data_list);
  }
}
