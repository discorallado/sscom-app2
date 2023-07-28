<?php

namespace App\Filament\Resources\Manager\PaymentResource\Pages;

use App\Filament\Resources\Manager\PaymentResource;
use App\Models\Manager\Payment;
use Closure;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
// use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;

class ListPayments extends ListRecords
{
  protected static string $resource = PaymentResource::class;

  protected function getActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->slideOver(),
    ];
  }

  protected function getTableRecordClassesUsing(): ?Closure
  {
    return fn (Model $record) => match ($record->doc) {
      null => [
        'priority',
        'dark:border-orange-300' => config('tables.dark_mode'),
      ],
      default => $record->mission,
    };
  }

  protected function getTableQuery(): Builder
  {
    return PaymentResource::getEloquentQuery();
  }
  //   protected function getTableQuery(): Builder
  //   {
  //     return Payment::select(
  //       DB::raw('MIN(id) as id'),
  //       DB::raw('DATE(created_at) as date'),
  //       DB::raw('count(*) as total')
  //     )
  //       ->orderBy('fecha', 'DESC')
  //       ->groupBy('manager_bill_id');
  //   }
}
