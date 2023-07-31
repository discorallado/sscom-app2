<?php

namespace App\Filament\Resources\Manager\CustomerResource\Pages;

use App\Filament\Resources\Manager\CustomerResource;
use Filament\Resources\Pages\Page;

class CustomerReport extends Page
{

  protected static string $resource = CustomerResource::class;

  protected static string $view = 'filament.resources.manager.customer-resource.pages.customer-report';

  protected function getHeaderWidgets(): array
  {
    return [
      CustomerResource\Widgets\RelatedWorks::class,
      CustomerResource\Widgets\RelatedCotizations::class,
      CustomerResource\Widgets\RelatedBills::class,
    ];
  }
}
