<?php

namespace App\Filament\Resources\LogResource\Widgets;

use App\Filament\Resources\LogResource;
use App\Models\Log;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Widgets\Concerns\CanFetchEvents;
use Filament\Forms;
use Filament\Resources\Form;


class CalendarWidget extends FullCalendarWidget
{
  protected $listeners = ['updateCalendarWidget' => '$refresh'];

  //protected static string $view = 'filament.resources.log-resource.widgets.calendar-widget';

  public function getViewData(): array
  {
    $events = [];
    $logs = Log::all();
    // dd($logs);
    foreach ($logs as $log) {
      $events[] = [
        'id' => $log->id,
        'title' => $log->title,
        'start' => Carbon::parse($log->start),
        'end' => Carbon::parse($log->end),
      ];
    }
    // dd($events);
    return $events;
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
  protected static function getEditEventFormSchema(): array
  {
    return [
      Forms\Components\Hidden::make('id'),
      Forms\Components\TextInput::make('title')
        ->required(),
      Forms\Components\DateTimePicker::make('start')
        ->required(),
      Forms\Components\DateTimePicker::make('end')
        ->default(null),
    ];
  }
  public function createEvent(array $data): void
  {
    // Create the event with the provided $data.
    // dd($this);
    $dataFiltered = [
      'title' => $data['title'],
      'start' => $data['start'],
      'end' => $data['end'],
    ];
    if (Log::create($dataFiltered)) {
      Notification::make()
        ->title('Evento editado')
        ->success()
        ->actions([
          Action::make('view')
            ->button()
            ->url(route('filament.resources.logs.index')),
        ])
        ->persistent()
        ->send();
    }
  }
  public function editEvent(array $data): void
  {
    $event = Log::find($data['id']);
    $event->title = $data['title'];
    $event->start = $data['start'];
    $event->end = $data['end'];
    if ($event->save()) {
      Notification::make()
        ->title('Evento editado')
        ->success()
        ->actions([
          Action::make('view')
            ->button()
            ->url(route('filament.resources.logs.index')),
        ])
        ->persistent()
        ->send();
    }
  }
}
