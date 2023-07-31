<?php

namespace App\Filament\Resources\Manager\CustomerResource\Widgets;

use App\Filament\Resources\Manager\CustomerResource;
use App\Filament\Resources\Manager\WorkResource;
use App\Models\Manager\Cotization;
use App\Models\Manager\Customer;
use App\Models\Manager\Work;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Widget;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

class RelatedWorks extends TableWidget
{

  public ?Model $record = null;

  public $widgetData;

  protected static string $view = 'filament.resources.manager.customer-resource.widgets.related-works';

  protected int | string | array $columnSpan = 'full';

  protected static ?int $sort = 1;

  protected static ?string $heading = 'Proyectos relacionadas';

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
    return Work::where('manager_customer_id', '=', $this->widgetData['id'])
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }

  protected function getTableColumns(): array
  {
    return [
      Tables\Columns\TextColumn::make('title')
        ->sortable(),
      Tables\Columns\TextColumn::make('customer.name')
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
