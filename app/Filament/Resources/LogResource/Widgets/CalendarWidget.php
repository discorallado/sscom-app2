<?php

namespace App\Filament\Resources\LogResource\Widgets;

use Filament\Widgets\Widget;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;


class CalendarWidget extends Widget
{
  protected static string $view = 'filament.resources.log-resource.widgets.calendar-widget';

  public function getViewData(): array
  {
    return [
      [
        'id' => 1,
        'title' => 'Breakfast!',
        'start' => now()
      ],
      [
        'id' => 2,
        'title' => 'Meeting with Pamela',
        'start' => now()->addDay(),
        'url' => 'https://some-url.com',
        'shouldOpenInNewTab' => true,
      ]
    ];
  }

  /**
   * FullCalendar will call this function whenever it needs new event data.
   * This is triggered when the user clicks prev/next or switches views on the calendar.
   */
  public function fetchEvents(array $fetchInfo): array
  {
    // You can use $fetchInfo to filter events by date.
    return [];
  }
}
