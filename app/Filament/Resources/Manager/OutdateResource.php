<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\OutdateResource\Pages;
use App\Filament\Resources\Manager\OutdateResource\RelationManagers;
use App\Models\Manager\Outdate;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutdateResource extends Resource
{
  protected static ?string $model = Outdate::class;

  protected static ?int $navigationSort = 9;

  protected static ?string $slug = 'manager/outdates';

  protected static ?string $modelLabel = 'Archivado';

  protected static ?string $pluralModelLabel = 'Archivados';

  protected static ?string $recordTitleAttribute = 'num_doc';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-clock';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('tipo')
          ->required()
          ->maxLength(2),
        Forms\Components\TextInput::make('tipo_doc')
          ->required()
          ->maxLength(191),
        Forms\Components\Textarea::make('file')
          ->maxLength(65535),
        Forms\Components\Textarea::make('num_doc')
          ->maxLength(65535),
        Forms\Components\DatePicker::make('date')
          ->required(),
        Forms\Components\TextInput::make('excento')
          ->required(),
        Forms\Components\TextInput::make('neto')
          ->required(),
        Forms\Components\Textarea::make('observaciones')
          ->maxLength(65535),
        Forms\Components\Select::make('user_id')
          ->relationship('user', 'name'),
        Forms\Components\TextInput::make('manager_customer_id'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('tipo'),
        Tables\Columns\TextColumn::make('tipo_doc'),
        Tables\Columns\TextColumn::make('file'),
        Tables\Columns\TextColumn::make('num_doc'),
        Tables\Columns\TextColumn::make('date')
          ->date(),
        Tables\Columns\TextColumn::make('excento'),
        Tables\Columns\TextColumn::make('neto'),
        Tables\Columns\TextColumn::make('observaciones'),
        Tables\Columns\TextColumn::make('user.name'),
        Tables\Columns\TextColumn::make('manager_customer_id'),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime(),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime(),
        Tables\Columns\TextColumn::make('deleted_at')
          ->dateTime(),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
        Tables\Actions\ForceDeleteBulkAction::make(),
        Tables\Actions\RestoreBulkAction::make(),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListOutdates::route('/'),
      'create' => Pages\CreateOutdate::route('/create'),
      'view' => Pages\ViewOutdate::route('/{record}'),
      'edit' => Pages\EditOutdate::route('/{record}/edit'),
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
