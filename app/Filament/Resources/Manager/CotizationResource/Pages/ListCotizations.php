<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\View;
use Illuminate\Contracts\View\View as ViewView;

class ListCotizations extends ListRecords
{
  protected static string $resource = CotizationResource::class;
  public array $data_list = [
    'calc_columns' => [
      'total_price',
    ],
    'calc_columns_format' => [
      'money_clp',
    ],
  ];

  protected function getActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  protected function getHeaderWidgets(): array
  {
    return CotizationResource::getWidgets();
  }

  protected function getTableContentFooter(): ?ViewView
  {
    return view('table.footer', $this->data_list);
  }
}
