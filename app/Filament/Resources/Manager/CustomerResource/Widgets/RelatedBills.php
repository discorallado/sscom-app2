<?php

namespace App\Filament\Resources\Manager\CustomerResource\Widgets;

use App\Models\Manager\Work;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RelatedBills extends BaseWidget
{
  public ?Model $record = null;

  public $widgetData;

  protected static string $view = 'filament.resources.manager.customer-resource.widgets.related-works';

  protected int | string | array $columnSpan = 'full';

  protected static ?int $sort = 1;

  protected static ?string $heading = 'Cotizaciones relacionadas';

  public function mount(): void
  {
    $this->widgetData = [
      'id' => '103',
    ];
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
    return Work::with('Bill')->where('manager_customer_id', '=', $this->widgetData['id']);
  }

  protected function getTableColumns(): array
  {
    return [
      Tables\Columns\TextColumn::make('cotization.codigo')
        ->sortable(),
      Tables\Columns\TextColumn::make('cotization.total_price')
        ->sortable(),
    ];
  }

  protected function getTableActions(): array
  {
    return [
      Tables\Actions\Action::make('open')
        ->label('Abrir')
      // ->url(fn (Customer $record): string => CustomerResource::getUrl('edit', ['record' => $record])),
    ];
  }
}
