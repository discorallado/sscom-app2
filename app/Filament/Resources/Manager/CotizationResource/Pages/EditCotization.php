<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use App\Models\Manager\Bill;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class EditCotization extends EditRecord
{
  protected static string $resource = CotizationResource::class;

  protected function getActions(): array
  {
    return [
      Actions\DeleteAction::make(),
      Actions\RestoreAction::make(),
      Actions\ForceDeleteAction::make(),
    ];
  }


  //   protected function handleRecordUpdate(Model $record, array $data): Model
  //   {
  //     $facturas = Bill::where('manager_cotization_id', '=', $record->id)->count();
  //     $registrado = $record->total_price;
  //     $nuevo = $data['total_price'];
  //     // dd($facturas);
  //     dd(($facturas > 0) && ($registrado != $nuevo));
  //     if (($facturas > 0) && ($registrado != $nuevo)) {
  //       Notification::make()
  //         ->title('Factura asociada')
  //         ->warning()
  //         ->body('La cotizacion modificada tiene una o mas facturas asociadas, desea modificar los montos de éstas?')
  //         ->actions([
  //           Action::make('view')
  //             ->button(),
  //           Action::make('undo')
  //             ->color('secondary'),
  //         ])
  //         ->send();
  //       return $record;
  //     }
  //     // $record->update($data);

  //   }

  //   protected function onValidationError(ValidationException $exception): void
  //   {
  //     Notification::make()
  //       ->title('Factura asociada')
  //       ->warning()
  //       ->body('La cotizacion modificada tiene una o mas facturas asociadas, desea modificar los montos de éstas?')
  //       ->actions([
  //         Action::make('view')
  //           ->button(),
  //         Action::make('undo')
  //           ->color('secondary'),
  //       ])
  //       ->send();
  //   }
}
