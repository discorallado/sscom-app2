<?php

namespace App\Filament\Resources\Manager\WorkResource\Widgets;

use Filament\Widgets\LineChartWidget;

class ChartPayments extends LineChartWidget
{
  protected static ?string $heading = 'Chart';

  protected function getFilters(): ?array
  {
    return [
      'today' => 'Today',
      'week' => 'Last week',
      'month' => 'Last month',
      'year' => 'This year',
    ];
  }

  public function getDescription(): ?string
  {
    return 'The number of blog posts published per month.';
  }

  protected function getData(): array
  {
    return [
      'datasets' => [
        [
          'label' => 'Customers',
          'data' => [4344, 5676, 6798, 7890, 8987, 9388, 10343, 10524, 13664, 14345, 15753],
        ],
      ],
      'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    ];
  }
}
