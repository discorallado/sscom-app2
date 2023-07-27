<?php

namespace App\Filament\Resources\Manager\CotizationResource\RelationManagers;

use App\Filament\Resources\Manager\BillResource;
use App\Models\Manager\Bill;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillsRelationManager extends RelationManager
{
  protected static string $relationship = 'bill';

  protected static ?string $modelLabel = 'Factura';

  protected static ?string $pluralModelLabel = 'Facturas';


  protected static ?string $recordTitleAttribute = 'fecha';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('fecha')
          ->required()
          ->maxLength(255),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('fecha')
          ->searchable()
          ->sortable()
          ->date(),
        Tables\Columns\BadgeColumn::make('doc')
          ->searchable()
          ->color('secondary')
          ->sortable(),

        Tables\Columns\BadgeColumn::make('tipo')
          ->searchable()
          ->sortable()
          ->colors([
            'success' => 'VENTA',
            'warning' => 'COSTO',
          ]),
        Tables\Columns\TextColumn::make('total_price')
          ->searchable()
          ->sortable()
          ->label('Valor')
          ->money('clp'),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make()
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make(),
      ])
      ->actions([
        Tables\Actions\Action::make('open')
          ->url(fn (Bill $record): string => BillResource::getUrl('view', ['record' => $record])),
      ]);
  }

  protected function getTableQuery(): Builder
  {
    return parent::getTableQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
