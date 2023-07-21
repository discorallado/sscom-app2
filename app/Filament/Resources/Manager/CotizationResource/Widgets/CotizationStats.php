<?php

namespace App\Filament\Resources\Manager\CotizationResource\Widgets;

use App\Models\Manager\Cotization;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CotizationStats extends BaseWidget
{
    protected function getCards(): array
    {
        $cotizationData = Trend::model(Cotization::class)
            ->between(
                start: now()->subWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            Card::make('Cotizaciones', Cotization::where('fecha', '>=', now()->subWeek())->count())
            ->color('success')
                ->chart(
                    $cotizationData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),

            Card::make('Cotizaciones activas', Cotization::where('vencimiento', '>=', now())->count(). ' de ' . Cotization::all()->count()),

            Card::make('Valor promedio', '$ '.number_format(Cotization::avg('total_price'), 0,0,'.')),
            Card::make('Valor maximo', '$ '.number_format(Cotization::max('total_price'), 0,0,'.')),
        ];
    }
}
