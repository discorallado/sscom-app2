<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\ProductResource\Pages;
use App\Filament\Resources\Manager\ProductResource\RelationManagers;
use App\Filament\Resources\Manager\ProductResource\Widgets\ProductStats;
use App\Models\Blog\Category;
use App\Models\Manager\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TagsInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
  protected static ?string $model = Product::class;

  protected static ?int $navigationSort = 7;

  protected static ?string $slug = 'manager/products';

  protected static ?string $modelLabel = 'Producto';

  protected static ?string $pluralModelLabel = 'Productos';

  protected static ?string $recordTitleAttribute = 'nombre';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-lightning-bolt';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('nombre')
          ->required()
          ->maxLength(191),
        Forms\Components\TextInput::make('precio_stock')
          ->required(),
        Forms\Components\TextInput::make('unidad')
          ->maxLength(191),

        Forms\Components\MultiSelect::make('categories')
                                    ->relationship('categories', 'name')
                                    ->required(),


      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('nombre')
          ->searchable()
          ->sortable()
          ->limit(35)
          ->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            if (strlen($state) <= $column->getLimit()) {
              return null;
            }
            return $state;
          }),
        Tables\Columns\TextColumn::make('precio_stock')
          ->searchable()
          ->money('clp')
          ->sortable(),
        Tables\Columns\TextColumn::make('unidad')
          ->size('sm')
          ->searchable()
          ->sortable(),
        Tables\Columns\TagsColumn::make('categories.name')
          ->searchable()
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
      'index' => Pages\ManageProducts::route('/'),
    ];
  }

  public static function getWidgets(): array
  {
    return [
      ProductStats::class,
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
