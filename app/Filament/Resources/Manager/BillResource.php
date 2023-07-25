<?php

namespace App\Filament\Resources\Manager;

use App\Models\Manager\Cotization;
use App\Models\Manager\Product;
use App\Models\Manager\Customer;


use App\Filament\Resources\Manager\BillResource\Pages;
use App\Filament\Resources\Manager\BillResource\RelationManagers;
use App\Models\Manager\Bill;
use App\Models\Manager\Work;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use msuhels\editorjs\Forms\Components\EditorJs;

use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput\Mask;
use Illuminate\Support\HtmlString;
use Suleymanozev\FilamentRadioButtonField\Forms\Components\RadioButton;

class BillResource extends Resource
{
  protected static ?string $model = Bill::class;

  protected static ?int $navigationSort = 4;

  protected static ?string $slug = 'manager/bills';

  protected static ?string $modelLabel = 'Factura';

  protected static ?string $pluralModelLabel = 'Facturas';

  protected static ?string $recordTitleAttribute = 'fecha';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-inbox';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Detalles')
          ->columns(3)
          ->icon('heroicon-o-identification')
          ->schema([

            Forms\Components\DateTimePicker::make('fecha')
              ->label('Fecha emisión:')
              ->withoutSeconds()
              ->required()
              ->timezone('America/Santiago')
              ->default(\now()),

            Forms\Components\TextInput::make('doc')
              ->label('Numero de documento')
              ->hint('ej: FAC4321')
              ->default('FAC')
              ->autofocus()
              ->unique()
              ->required()
              ->prefixIcon('heroicon-o-hashtag')
              ->maxLength(191),

            RadioButton::make('tipo')
              ->columnSpan(3)
              ->columns(2)
              ->label('Tipo de factura')
              ->reactive()
              ->options([
                'VENTA' => 'Factura de VENTA',
                'COSTO' => 'Factura de COMPRA',
              ])
              ->descriptions([
                'VENTA' => 'La factura se guarda como venta.',
                'COSTO' => 'La factura se guarda como compra.',
              ])

          ]),
        Section::make('Venta')
          ->description('Factura de venta')
          ->icon('heroicon-o-chevron-double-up')
          ->columns(3)
          ->visible(fn (Closure $get) => $get('tipo') == 'VENTA')
          ->schema([

            Forms\Components\Select::make('manager_work_id')
              ->label('Trabajo')
              ->reactive()
              ->options(Work::all()->pluck('title', 'id')->toArray())
              ->afterStateUpdated(function (callable $set, callable $get) {
                $set('manager_cotization_id', null);
                $set('total_price', '0');
                $set('customer',  Work::find($get('manager_work_id'))?->manager_customer_id);
              })
              ->required()
              ->columnSpan(2),

            Forms\Components\Select::make('manager_cotization_id')
              ->label('Cotizacion')
              ->reactive()
              ->options(function (callable $get) {
                $work = Work::find($get('manager_work_id'));
                if (!$work) {
                  return Cotization::all()->pluck('codigo', 'id');
                } else {
                  return $work->cotization->pluck('codigo', 'id')->toArray();
                }
              })
              ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                $total_price =  (string)Cotization::find($state)->total_price;
                $set('total_price', $total_price);
                $set('customer',  Cotization::find($state)?->manager_customer_id);
              })
              ->columnSpan(1),

            Forms\Components\Select::make('customer')
              ->label('Cliente:')
              ->reactive()
              ->options(Customer::all()->pluck('name', 'id'))
              ->disabled()
              ->columnSpan(3),

          ]),

        Section::make('Gasto')
          ->icon('heroicon-o-chevron-double-down')
          ->description('Factura de compra')
          ->visible(fn (Closure $get) => $get('tipo') == 'COSTO')
          ->columns(3)
          ->schema([
            Forms\Components\Select::make('customer')
              ->label('Proveedor')
              ->options(Customer::all()->pluck('name', 'id'))
              ->columnSpan(3)
              ->createOptionForm([
                Forms\Components\TextInput::make('name')
                  ->required(),
                Forms\Components\TextInput::make('rut'),
              ])
              ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                return $action
                  ->modalHeading('Crear cliente')
                  ->modalButton('Crear cliente')
                  ->modalWidth('sm');
              })
              ->createOptionUsing(function (array $data) {
                if ($customer = Customer::create($data)) {
                  return $customer->id;
                }
              }),

            Forms\Components\Select::make('manager_work_id')
              ->label('Trabajo')
              ->options(Work::all()->pluck('title', 'id')->toArray())
              ->afterStateUpdated(function (callable $set, callable $get) {
                $set('manager_cotization_id', null);
                $set('customer',  Work::find($get('manager_work_id'))?->manager_customer_id);
              })
              ->columnSpan(2),

            Forms\Components\Select::make('manager_cotization_id')
              ->label('Cotizacion')
              ->options(function (callable $get) {
                $work = Work::find($get('manager_work_id'));
                if (!$work) {
                  return Cotization::all()->pluck('codigo', 'id');
                } else {
                  return $work->cotization->pluck('codigo', 'id')->toArray();
                }
              })
              ->columnSpan(1),

          ]),

        Section::make('Monto')
          ->icon('heroicon-o-cash')
          ->description('Factura de compra')
          ->schema([

            Forms\Components\Placeholder::make('neto')
              ->label('Precio Neto $:')
              ->reactive()
              ->columnSpan(1)
              ->content(function (callable $get, callable $set, $state) {
                return '$ ' . \number_format((int)$get('total_price') / 1.19, 0, '', '.');
              }),

            Forms\Components\Placeholder::make('iva')
              ->label('IVA $:')
              ->reactive()
              ->columnSpan(1)
              ->content(function (callable $get, callable $set, $state) {
                return '$ ' . \number_format((int)$get('total_price') * 0.1596638655, 0, '', '.');
              }),

            Forms\Components\TextInput::make('total_price')
              ->label('Precio total $:')
              ->reactive()
              ->columnSpan(1)
              ->default('0')
              ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0)),

          ])->columns(3),

        // Section::make('Descripcion')
        //   ->description('Observaciones y detalles')
        //   ->icon('heroicon-o-identification')
        //   ->schema([

        //     RichEditor::make('descripcion'),

        //     SpatieMediaLibraryFileUpload::make('file')
        //       ->label('Archivo djunto')
        //       ->preserveFilenames()
        //       ->enableOpen()
        //       ->enableDownload()
        //       ->columnSpan('full'),
        //   ])
        //   ->columns(1),

      ])
      ->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('created_at', 'desc')
      ->columns([
        Tables\Columns\TextColumn::make('fecha')
          ->sortable()
          ->date(),
        Tables\Columns\BadgeColumn::make('doc')
        ->color('primary')
          ->sortable(),

        Tables\Columns\BadgeColumn::make('tipo')
          ->sortable()
          ->colors([
                    'success' => 'VENTA',
                    'warning' => 'COSTO',
                  ]),

        Tables\Columns\TextColumn::make('work.title')
          ->sortable(),

        Tables\Columns\TextColumn::make('cotization.codigo')
          ->placeholder('S/C')
          ->sortable()
          ->size('sm'),

        Tables\Columns\TextColumn::make('total_price')
          ->sortable()
          ->label('Valor')
          ->money('clp'),

        Tables\Columns\TextColumn::make('payments_sum_abono')
          ->label('Pagos')
          ->sum('payments', 'abono')
          ->placeholder('$0')
          ->sortable()
          ->money('clp'),

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

  public static function getRelations(): array
  {
    return [
      RelationManagers\PaymentsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListBills::route('/'),
      'create' => Pages\CreateBill::route('/create'),
      'view' => Pages\ViewBill::route('/{record}'),
      'edit' => Pages\EditBill::route('/{record}/edit'),
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
