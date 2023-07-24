<?php

namespace App\Filament\Resources\Manager\ProductResource\Widgets;

use App\Models\Manager\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ProductStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Productos sin precio', Product::where('precio_stock', '<', 1)->count())
            ->color('danger'),
            Card::make('Productos sin unidad', Product::where('categoria', '=', NULL)->count())
            ->color('danger'),
            // Card::make('Product Inventory', Product::sum('qty')),
            Card::make('Average price', number_format(Product::avg('precio_stock'), 0,'','.')),
        ];
    }
}
