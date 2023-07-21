<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use App\Models\Manager\Bill;
use App\Models\Manager\Payment;
use Carbon\Carbon;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Database\Eloquent\Model;

class CreateCotization extends CreateRecord
{
  use HasWizard;

  protected static string $resource = CotizationResource::class;

  protected function afterCreate(): void
  {
    $Cotization = $this->record;
    Notification::make()
      ->title('Nueva cotizacion')
      ->icon('heroicon-o-shopping-bag')
      ->body("**{$Cotization->work->customer->name} Cotizationed {$Cotization->items->count()} items.**")
      ->actions([
        Action::make('View')
          ->url(CotizationResource::getUrl('edit', ['record' => $Cotization])),
      ])
      ->sendToDatabase(auth()->user());
  }
  protected function getSteps(): array
  {
    return [
      Step::make('Detalles')
        ->description('Ingrese datos de cotizacion y cliente')
        ->schema([
          Card::make(CotizationResource::getFormSchema())->columns(),
        ]),

      Step::make('Items')
        ->description('Agrege los item que se cotizaran')
        ->schema([
          Card::make(CotizationResource::getFormSchema('repetidor')),
        ]),
    ];
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $fecha = Carbon::parse($data['fecha']);
    $data['user_id'] = auth()->id();
    $data['vencimiento'] = $fecha->add((int)$data['validez'], 'day');
    return $data;
  }
}
