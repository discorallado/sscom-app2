<?php

namespace App\Filament\Resources\Manager\WorkResource\Widgets;

use App\Models\Manager\Payment;
use App\Models\Manager\Work;
use Filament\Widgets\LineChartWidget;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PaymentsStats extends StatsOverviewWidget
{
  protected $record, $work, $payments, $bills;

  protected static ?string $heading = 'Chart';

  protected static ?string $pollingInterval = null;

  public function mount(): void
  {
    parent::mount();
    $record = (int)request()?->segment(count(request()?->segments()) - 1);
    $work = Work::find($record);
    $payments = $work->Payments;
    $bills = $work->Bill;

    $this->record = $record;
    $this->work = $work;
    $this->payments = $payments;
    $this->bills = $bills;
  }

  protected function getCards(): array
  {

    $cotizationData = Trend::model(Payment::class)
      ->between(
        start: now()->subMonth(),
        end: now(),
      )
      ->dateColumn('fecha')
      ->perDay()
      ->count();
    // DD($payments->toArray());

    // dd($this);
    return [
      Card::make($this->work?->Customer->name, $this->work?->title),
      Card::make('Pagos', '$' . number_format($this->payments->sum('abono'), 0, 0, '.'))
        ->color('success')
        ->chart(
          $cotizationData
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray()
        ),
      Card::make('Total Facturado $:', '$' . number_format($this->bills->sum('total_price'), 0, 0, '.')),
    ];
  }
}
