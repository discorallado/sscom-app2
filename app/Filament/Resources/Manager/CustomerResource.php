<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\CustomerResource\Pages;
use App\Filament\Resources\Manager\CustomerResource\RelationManagers;
use App\Models\Manager\Customer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
  protected static ?string $model = Customer::class;

  protected static ?int $navigationSort = 6;

  protected static ?string $slug = 'manager/customers';

  protected static ?string $modelLabel = 'Cliente';

  protected static ?string $pluralModelLabel = 'Clientes';

  protected static ?string $recordTitleAttribute = 'name';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-user-circle';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Grid::make(2)
          ->schema([
            Forms\Components\TextInput::make('rut')
              ->required()
              ->regex('^(\d{1,3}(?:\.\d{1,3}){2}-[\dkK])$')
              ->maxLength(12)
              ->columnSpan(1),
          ]),
            Forms\Components\TextInput::make('name')
              ->required()
              ->columnSpan(2)
              ->maxLength(191),
            Forms\Components\TextInput::make('giro')
              ->required()
              ->columnSpan(1)
              ->maxLength(191),

        Forms\Components\MarkdownEditor::make('contacto')
          ->required()
          ->columnSpan('full'),




      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('rut'),
        Tables\Columns\TextColumn::make('name')
          ->words(4)
          ->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            if (str_word_count($state) <= 4) {
              return null;
            }
            return $state;
          }),

        Tables\Columns\TextColumn::make('contacto'),

        Tables\Columns\TextColumn::make('user.name')
          ->label('Creado por')
          ->toggleable(isToggledHiddenByDefault: true)
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Creado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->sortable(),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Modificado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->sortable(),
        Tables\Columns\TextColumn::make('deleted_at')
          ->label('Eliminado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->placeholder('Nunca')
          ->sortable(),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
        Tables\Actions\ForceDeleteAction::make(),
        Tables\Actions\RestoreAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
        Tables\Actions\ForceDeleteBulkAction::make(),
        Tables\Actions\RestoreBulkAction::make(),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageCustomers::route('/'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
