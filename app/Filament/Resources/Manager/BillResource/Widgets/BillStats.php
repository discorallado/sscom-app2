<?php

namespace App\Filament\Resources\Manager\BillResource\Widgets;

use App\Models\Manager\Bill;
use App\Models\Manager\Payment;
use Filament\Forms\Components\Grid;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BillStats extends BaseWidget
{
  protected function getCards(): array
  {
    // $BillData = Trend::model(Bill::class)
    //     ->between(
    //         start: now()->subMonth(),
    //         end: now(),
    //     )
    //     ->dateColumn('fecha')
    //     ->perDay()
    //     ->count();
      $count_venta_mes = (int)Bill::where('tipo', '=', 'VENTA')->where('fecha', '>=', now()->subMonth())->count();
      $count_compra_mes = (int)Bill::where('tipo', '=', 'COSTO')->where('fecha', '>=', now()->subMonth())->count();

      $venta_mes = (int)Bill::where('tipo', '=', 'VENTA')->where('fecha', '>=', \now()->subMonth())->sum('total_price');
      $compra_mes = (int)Bill::where('tipo', '=', 'COSTO')->where('fecha', '>=', \now()->subMonth())->sum('total_price');

      $pagos_mes = (int)Payment::where('manager_bill_id', '!=', null)->sum('abono');
      $deuda_mes = (int)Bill::where('tipo', '=', 'VENTA')->sum('total_price') - (int)Payment::where('manager_bill_id', '!=', null)->sum('abono');

    return [
      // Card::make('Cotizaciones el último mes', Bill::where('fecha', '>=', now()->subMonth())->count())
      // ->color('success')
      //     ->chart(
      //         $BillData
      //             ->map(fn (TrendValue $value) => $value->aggregate)
      //             ->toArray()
      //     ),
        Card::make(
          'Ventas mes'. ' (' . $count_venta_mes . ' facturas)',
          '$' . number_format($venta_mes, 0, 0, '.')
        ),

        Card::make(
          'Compras mes'. ' (' . $count_compra_mes . ' facturas)',
          '$' . number_format($compra_mes, 0, 0, '.')
        ),

        Card::make(
          'Total pagado mes',
         '$' . number_format($pagos_mes, 0, 0, '.')
        ),

        Card::make(
          'Total adeudado mes',
         '$' . number_format($deuda_mes, 0, 0, '.')
        ),

        // Card::make('Valor promedio', '$ '.number_format(Bill::avg('total_price'), 0,0,'.')),
        // Card::make('Valor maximo', '$ '.number_format(Bill::max('total_price'), 0,0,'.')),
    //   Card::make('Average time on page', '3:12')
    //         ->description('3% increase')
    //         ->descriptionIcon('heroicon-s-trending-up')
    //         ->color('success'),

    ];
  }

}
