<?php

namespace App\Filament\Resources\Manager\WorkResource\Pages;

use App\Filament\Resources\Manager\WorkResource;
use App\Filament\Resources\Manager\WorkResource\Widgets\ChartPayments;
use App\Filament\Resources\Manager\WorkResource\Widgets\PaymentsStats;
use App\Filament\Resources\Manager\WorkResource\Widgets\RelatedBill;
use App\Filament\Resources\Manager\WorkResource\Widgets\RelatedCotization;
use App\Filament\Resources\Manager\WorkResource\Widgets\RelatedPayments;
use Filament\Resources\Pages\Page;
use Filament\Widgets\Widget;

class ReportWork extends Page
{
  protected static string $resource = WorkResource::class;

  protected static ?string $navigationIcon = 'heroicon-o-home';

  protected static string $view = 'filament.resources.manager.work-resource.pages.report-work';

  protected static ?string $title = 'Informe de proyecto';

  protected static ?string $navigationLabel = 'Informe proyecto';


  //   protected int | string | array $columnSpan = 2;

  protected function getHeaderWidgets(): array
  {
    return [
      PaymentsStats::class,
      RelatedCotization::class,
      ChartPayments::class,
      RelatedBill::class,
      RelatedPayments::class,
    ];
  }
}
