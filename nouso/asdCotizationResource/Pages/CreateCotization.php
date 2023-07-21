<?php

namespace App\Filament\Resources\Manager\CotizationResource\Pages;

use App\Filament\Resources\Manager\CotizationResource;
use Filament\Pages\Actions;
use Filament\Forms\Components\Wizard\Step;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Forms\Components\Card;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;





class CreateCotization extends CreateRecord
{
    protected static string $resource = CotizationResource::class;

    protected function afterCreate(): void
    {
        $cotization = $this->record;

        Notification::make()
            ->title('New cotization')
            ->icon('heroicon-o-shopping-bag')
            ->body("**{$cotization->customer->name} cotizationed {$cotization->items->count()} items.**")
            ->actions([
                Action::make('View')
                    ->url(CotizationResource::getUrl('edit', ['record' => $cotization])),
            ])
            ->sendToDatabase(auth()->user());
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Cotization Details')
                ->schema([
                    Card::make(CotizationResource::getFormSchema())->columns(),
                ]),

            Step::make('Cotization Items')
                ->schema([
                    Card::make(CotizationResource::getFormSchema('items')),
                ]),
        ];
    }
}
