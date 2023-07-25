<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\PaymentResource\Pages;
use App\Filament\Resources\Manager\PaymentResource\RelationManagers;
use App\Models\Manager\Payment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Panel;


use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Models\Manager\Bill;
use App\Models\Manager\Cotization;
use App\Models\Manager\Work;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class PaymentResource extends Resource
{
  protected static ?string $model = Payment::class;

  protected static ?int $navigationSort = 5;

  protected static ?string $slug = 'manager/payments';

  protected static ?string $recordTitleAttribute = 'fecha';

  protected static ?string $modelLabel = 'Pago';

  protected static ?string $pluralModelLabel = 'Pagos';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-cash';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([


        Section::make('Datos')
          ->columns(3)
          ->icon('heroicon-o-identification')
          ->description('Detalles del pago.')
          ->schema([

            Forms\Components\DateTimePicker::make('fecha')
              ->label('Fecha emisión:')
              ->withoutSeconds()
              ->required()
              ->default(\now())
              ->columnSpan(1),

            Card::make()
              ->columns(3)
              ->schema([


                Forms\Components\Select::make('manager_work_id')
                  ->label('Trabajo:')
                  ->options(Work::all()->pluck('title', 'id')->toArray())
                  ->required()
                  ->reactive()
                  ->afterStateUpdated(function (callable $set, callable $get) {
                    $set('manager_cotization_id', null);
                    $set('manager_bill_id', null);
                    $set('total_price', '0');
                  })
                  ->columnSpan(1),

                Forms\Components\Select::make('manager_cotization_id')
                  ->label('Cotizacion:')
                  ->reactive()
                  ->disabled(fn (Closure $get) => $get('manager_work_id') ? false : true)
                  ->options(function (callable $get) {
                    $work = Work::find($get('manager_work_id'));
                    if (!$work) {
                      return Cotization::all()->pluck('codigo', 'id');
                    } else {
                      return $work->cotization->pluck('codigo', 'id')->toArray();
                    }
                  })
                  ->afterStateUpdated(function (callable $set, $state) {
                    $set('total_price',  (string)Cotization::find((int)$state)->total_price);
                  })
                  ->columnSpan(1),

                Forms\Components\Select::make('manager_bill_id')
                  ->label('Factura:')
                  ->reactive()
                  ->disabled(fn (Closure $get) => $get('manager_work_id') ? false : true)
                  ->options(function (callable $get) {
                    $work = Work::find($get('manager_work_id'));
                    // dd($work);
                    if ($work) {
                      return $work->bill->pluck('doc', 'id')->toArray();
                    } else {
                      return Bill::all()->pluck('doc', 'id');
                    }
                  })
                  ->afterStateUpdated(function (Closure $set, Closure $get, $state) {
                    if ($get('manager_work_id')) {
                      $set('total_price',  (string)Bill::find((int)$state)?->total_price);
                    }
                  })
                  ->columnSpan(1),

              ]),
          ]),

        Section::make('Monto')
          ->icon('heroicon-o-cash')
          ->columns(3)
          ->description('Valores del pago.')
          ->inlineLabel()
          ->schema([


            Forms\Components\TextInput::make('total_price')
              ->label('A pagar:')
              ->default('0')
              ->numeric()
              ->required()
              ->reactive()
              ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
              ->afterStateUpdated(function ($state, callable $set, callable $get) {
                $set('saldo', (string)floor((int)$get('total_price') - (int)$get('abono')));
              }),
            Forms\Components\TextInput::make('abono')
              ->label('Abono:')
              ->default('0')
              ->required()
              ->reactive()
              ->afterStateUpdated(function ($state, callable $set, callable $get) {
                $set('saldo', (string)floor((int)$get('total_price') - (int)$get('abono')));
              })
              ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
              ->columnSpan(1),

            Forms\Components\TextInput::make('saldo')
              ->label('Saldo:')
              ->disabled()
              ->required()
              ->reactive()
              ->default('0')
              ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
              ->columnSpan(1),
            //   ]),

          ]),
        Section::make('Descripcion')
          ->icon('heroicon-o-document-text')
          ->description('Datos adicionales.')
          ->schema([

            Forms\Components\RichEditor::make('descripcion')
              ->label('Descripcion:')
              ->columnSpan('full')
              ->disableToolbarButtons([
                'attachFiles',
                'codeBlock',
              ]),

            SpatieMediaLibraryFileUpload::make('file')
              ->label('Adjunto:')
              ->preserveFilenames()
              ->enableOpen()
              ->enableDownload()
              ->columnSpan(1),
          ]),
      ])
      ->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Split::make([

          Grid::make(6)
            ->schema([
              Tables\Columns\BadgeColumn::make('bill.tipo')
                ->weight('bold')
                ->colors([
                  'warning' => 'COSTO',
                  'success' => 'VENTA',
                ])
                ->icon('heroicon-o-document-text'),

              Stack::make([
                Tables\Columns\TextColumn::make('bill.work.customer.name')
                  ->sortable()
                  ->icon('heroicon-s-user-group')
                  ->size('sm'),
                Tables\Columns\TextColumn::make('bill.work.title')
                  ->sortable()
                  ->icon('heroicon-o-briefcase'),
                // Tables\Columns\TextColumn::make('bill.work.cotization.codigo'),
              ])
                ->columnSpan(2),

              Stack::make([
                //   Tables\Columns\TextColumn::make('tipo')
                Tables\Columns\BadgeColumn::make('bill.doc')
                  ->weight('bold')
                //   ->color(function (Model $record) {
                //     if ($record->Bill->tipo == "VENTA") {
                //       return "success";
                //     }
                //     return "warning";
                //   })
                  ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('fecha')
                  ->sortable()
                  ->date(),
              ])
                ->columnSpan(1),

              Tables\Columns\TextColumn::make('abono')
                ->description('Abono')
                ->columnSpan(1)
                ->money('clp'),

              Tables\Columns\TextColumn::make('saldo')
                ->description('Saldo')
                ->columnSpan(1)
                ->money('clp'),
            ]),
        ]),

        // Tables\Columns\TextColumn::make('fecha')
        //   ->date(),
        // Tables\Columns\TextColumn::make('tipo'),

        // // Tables\Columns\TextColumn::make('monto'),
        // // Tables\Columns\TextColumn::make('num_doc'),
        // // Tables\Columns\TextColumn::make('detalles')
        // // ->wrap(),
        // // Tables\Columns\TextColumn::make('file'),
        // Tables\Columns\TextColumn::make('total_price')
        //   ->money('clp'),
        // // Tables\Columns\TextColumn::make('abono'),
        // Tables\Columns\TextColumn::make('saldo')
        //   ->money('clp'),
        // // Tables\Columns\TextColumn::make('observaciones'),
        // // Tables\Columns\TextColumn::make('user.name'),
        // Tables\Columns\TextColumn::make('customer.name')
        //   ->words(2),
      ])
      ->defaultSort('created_at', 'desc')
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\EditAction::make()
          ->slideOver(),
        Tables\Actions\DeleteAction::make(),
        Tables\Actions\ForceDeleteAction::make(),
        Tables\Actions\RestoreAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
        Tables\Actions\ForceDeleteBulkAction::make(),
        Tables\Actions\RestoreBulkAction::make(),
        FilamentExportBulkAction::make('export'),
      ])
      ->headerActions([
        FilamentExportHeaderAction::make('export')
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
      'index' => Pages\ManagePayments::route('/'),
    ];
    // return [
    //   'index' => Pages\ListPayments::route('/'),
    //   'create' => Pages\CreatePayment::route('/create'),
    //   'view' => Pages\ViewPayment::route('/{record}'),
    //   'edit' => Pages\EditPayment::route('/{record}/edit'),
    // ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
