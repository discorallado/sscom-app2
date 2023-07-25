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
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
              //   ->regex('^(\d{1,3}(?:\.\d{1,3}){2}-[\dkK])$')
              ->maxLength(12)
              ->columnSpan(1),
          ]),
        Forms\Components\TextInput::make('name')
          ->required()
          ->columnSpan(2)
          ->maxLength(191),
        Forms\Components\TextInput::make('giro')
          //   ->required()
          ->columnSpan(1)
          ->maxLength(191),

        Forms\Components\MarkdownEditor::make('contacto')
          //   ->required()
          ->columnSpan('full'),




      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Split::make([

          Grid::make(5)
            ->schema([

              Stack::make([
                Tables\Columns\TextColumn::make('name')
                  ->sortable()
                  ->icon('heroicon-s-user-group')
                  ->size('sm'),
                Tables\Columns\TextColumn::make('giro')
                  ->sortable()
                  ->icon('heroicon-o-briefcase'),
                // Tables\Columns\TextColumn::make('bill.work.cotization.codigo'),
              ])
                ->columnSpan(2),

              Stack::make([
                //   Tables\Columns\TextColumn::make('tipo')
                Tables\Columns\TextColumn::make('bill.doc')->weight('bold')
                  ->icon('heroicon-o-document-text'),
                Tables\Columns\TextColumn::make('fecha')
                  ->sortable()
                  ->date(),
              ])
                ->columnSpan(1),


              Tables\Columns\TextColumn::make('rut')
                ->columnSpan(1),


              Tables\Columns\TextColumn::make('saldo')
                ->description('Saldo')
                ->columnSpan(1)
                ->money('clp'),

            ]),
        ]),
        // Tables\Columns\TextColumn::make('fecha')
        //     ->date(),
        // Tables\Columns\TextColumn::make('tipo'),

        // // Tables\Columns\TextColumn::make('monto'),
        // // Tables\Columns\TextColumn::make('num_doc'),
        // // Tables\Columns\TextColumn::make('detalles')
        // // ->wrap(),
        // // Tables\Columns\TextColumn::make('file'),
        // Tables\Columns\TextColumn::make('total_price')
        // ->money('clp'),
        // // Tables\Columns\TextColumn::make('abono'),
        // Tables\Columns\TextColumn::make('saldo')
        // ->money('clp'),
        // // Tables\Columns\TextColumn::make('observaciones'),
        // // Tables\Columns\TextColumn::make('user.name'),
        // Tables\Columns\TextColumn::make('customer.name')
        // ->words(2),
      ])
      ->defaultSort('created_at', 'desc')
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
