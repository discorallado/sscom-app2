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

    return [
      // Card::make('Cotizaciones el último mes', Bill::where('fecha', '>=', now()->subMonth())->count())
      // ->color('success')
      //     ->chart(
      //         $BillData
      //             ->map(fn (TrendValue $value) => $value->aggregate)
      //             ->toArray()
      //     ),

        Card::make(
          'Facturas de Venta este mes',
          Bill::where('tipo', '=', 'VENTA')
            ->where('fecha', '>=', \now()->subMonth())->count() . '/' . Bill::all()->count()
        ),

        Card::make(
          'Facturas de Compra',
          Bill::where('tipo', '=', 'COSTO')->count() . '/' . Bill::all()->count()
        ),

        Card::make(
          'Total facturas compra',
          '$' . number_format(Bill::where('tipo', '=', 'COSTO')->sum('total_price'), 0, 0, '.')
        ),

        Card::make(
          'Total facturas venta',
          '$' . number_format(Bill::where('tipo', '=', 'VENTA')->sum('total_price'), 0, 0, '.')
        ),

        Card::make(
          'Total pagos',
         '$' . number_format((int)Payment::where('manager_bill_id', '!=', null)->sum('abono'), 0, 0, '.')
        ),

        Card::make(
          'Total adeudado',
         '$' . number_format( Bill::where('tipo', '=', 'VENTA')->sum('total_price') - Payment::where('manager_bill_id', '!=', null)->sum('abono'), 0, 0, '.')
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
