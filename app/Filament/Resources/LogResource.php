<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogResource\Pages;
use App\Filament\Resources\LogResource\RelationManagers;
use App\Filament\Resources\LogResource\Widgets\CalendarWidget;
use App\Models\Log;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogResource extends Resource
{
  protected static ?string $model = Log::class;

  protected static ?string $navigationIcon = 'heroicon-o-collection';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('title')
          ->required()
          ->maxLength(191),
        Forms\Components\TextInput::make('color')
          ->maxLength(50),
        Forms\Components\DatePicker::make('date')
          ->required(),
        Forms\Components\Textarea::make('text')
          ->maxLength(65535),
        Forms\Components\Select::make('user_id')
          ->relationship('user', 'name'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->label('Titulo')
          ->limit(15)
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('date')
          ->label('Fecha')
          ->date()
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('text')
          ->label('Fecha')
          ->words(3)
          ->searchable()
          ->sortable(),


        Tables\Columns\TextColumn::make('user.name')
          ->label('Creado por')
          ->toggleable(isToggledHiddenByDefault: true)
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Creado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Modificado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('deleted_at')
          ->label('Eliminado el')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true)
          ->placeholder('Nunca')
          ->searchable()
          ->sortable(),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
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
      'index' => Pages\ManageLogs::route('/'),
    ];
  }

  public static function getWidgets(): array
  {
    return [
      CalendarWidget::class,
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
