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
use Filament\Forms\Components\Component;
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

        Forms\Components\Group::make()
          ->columns(5)
          ->schema([
            // Forms\Components\Card::make()
            //   ->columnSpan(3)
            //   ->columns(1)
            //   ->schema([
            Section::make('Datos')
              ->columns(3)
              ->columnSpan(['lg' => fn (?Payment $record) => $record === null ? 5 : 3])
              ->icon('heroicon-o-identification')
              ->description('Detalles del pago.')
              ->schema([

                Forms\Components\DateTimePicker::make('fecha')
                  ->label('Fecha emisión:')
                  ->withoutSeconds()
                  ->required()
                  ->default(\now())
                  ->columnSpan(2),

                Card::make()
                  ->columns(2)
                  ->columnSpan('full')
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
                      ->columnSpan(2),

                    Forms\Components\Select::make('manager_cotization_id')
                      ->label('Cotizacion:')
                      ->reactive()
                      ->disabled(fn (Closure $get) => $get('manager_work_id') ? false : true)
                      ->options(function (callable $get) {
                        if ($get('manager_work_id')) {
                          $work = Work::find($get('manager_work_id'));
                          if (!$work) {
                            return Cotization::all()->pluck('codigo', 'id');
                          } else {
                            return $work->cotization->pluck('codigo', 'id')->toArray();
                          }
                        }
                      })
                      ->afterStateUpdated(function (callable $set, $state) {
                        $set('total_price',  (string)Cotization::find((int)$state)->total_price);
                      })
                      ->helperText(function (callable $get) {
                        if ($get('manager_cotization_id')) {
                          $val = (string)Cotization::find((int)$get('manager_cotization_id'))->total_price;
                          return new HtmlString('<a wire:click="$set(\'mountedActionData.total_price\', \'' . $val . '\')" class="text-xs cursor-pointer">[Total $' . number_format($val, 0, 0, '.') . ' usar este valor]</a>');
                        }
                      })
                      ->columnSpan(1),

                    Forms\Components\Select::make('manager_bill_id')
                      ->label('Factura:')
                      ->reactive()
                      ->disabled(fn (Closure $get) => $get('manager_work_id') ? false : true)
                      ->options(function (callable $get) {
                        if ($get('manager_cotization_id')) {
                          $bill = Bill::where('manager_cotization_id', '=', $get('manager_cotization_id'));
                          if ($bill) {
                            return  $bill->pluck('doc', 'id');
                          }
                          return Bill::where('manager_work_id', '=', $get('manager_work_id'))->pluck('doc', 'id');
                        }
                        return Bill::where('manager_work_id', '=', $get('manager_work_id'))->pluck('doc', 'id');
                      })
                      ->helperText(function ($state) {
                        if ($state) {
                          $consulta = DB::table('manager_payments')
                            ->where('manager_bill_id', '=', $state)
                            ->orderBy('fecha', 'DESC')
                            ->get('saldo');
                          if (count($consulta) > 0) {
                            $val = (string)$consulta[0]->saldo;
                          } else {
                            $val = (string)Bill::find((int)$state)->total_price;
                          }
                          return new HtmlString('<a wire:click="$set(\'mountedActionData.total_price\', \'' . $val . '\')" class="text-xs cursor-pointer">[Saldo $' . number_format($val, 0, 0, '.') . ' usar este valor]</a>');
                        }
                      })
                      ->afterStateUpdated(function (Closure $set, Closure $get, $state) {
                        // if ($get('manager_work_id')) {
                        $consulta = DB::table('manager_payments')
                          ->where('manager_bill_id', '=', $state)
                          ->orderBy('fecha', 'DESC')
                          ->get('saldo');
                        if (count($consulta) > 0) {
                          $set('total_price',  (string)$consulta[0]->saldo);
                        }
                        // }
                      })
                      ->columnSpan(1),
                  ]),
              ]),
            //   ]),

            Forms\Components\Card::make()
              ->columns(1)
              ->columnSpan(2)
              ->hidden(fn (?Payment $record) => $record === null)
              ->schema([
                Forms\Components\Placeholder::make('created_at')
                  ->label('Creado')
                  ->content(fn (Payment $record): ?string => $record->created_at?->diffForHumans() . ' (' . $record->created_at->format('H:i d-m-Y') . ')'),
                Forms\Components\Placeholder::make('updated_at')
                  ->label('Última modificación')
                  ->content(fn (Payment $record): ?string => $record->updated_at?->diffForHumans() . ' (' . $record->updated_at->format('H:i d-m-Y') . ')'),
              ]),
          ])
          ->columnSpan(4),



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
          ->collapsible()
          ->collapsed()
          ->schema([

            Forms\Components\RichEditor::make('descripcion')
              ->label('Descripcion:')
              ->columnSpan('full')
              ->disableToolbarButtons([
                'attachFiles',
                'codeBlock',
                'h1',
                'h2',
                'blockquote'
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
          Grid::make(12)
            ->schema([
              Stack::make([
                Tables\Columns\BadgeColumn::make('fecha')
                  ->color('primary')
                  ->sortable()
                  ->searchable()
                  ->date(),
                Tables\Columns\BadgeColumn::make('bill.doc')
                  ->weight('bold')
                  ->sortable()
                  ->searchable()
                  ->color('warning')
                  ->icon('heroicon-o-document-text'),

              ])
                ->columnSpan(2),

              Stack::make([
                Tables\Columns\TextColumn::make('work.customer.name')
                  ->sortable()
                  ->searchable()
                  ->icon('heroicon-s-user-group')
                  ->size('md'),
                Tables\Columns\TextColumn::make('work.title')
                  ->sortable()
                  ->searchable()
                  ->icon('heroicon-o-briefcase')
                  ->size('sm'),
                Tables\Columns\BadgeColumn::make('cotization.codigo')
                  ->weight('bold')
                  ->sortable()
                  ->searchable()
                  ->color('secodary')
                  ->icon('heroicon-o-document-text')
                  ->size('sm'),
                // Tables\Columns\TextColumn::make('bill.work.cotization.codigo'),
              ])
                ->columnSpan(4),

              Tables\Columns\TextColumn::make('total_price')
                ->description('Deuda')
                ->searchable()
                ->extraAttributes(['class' => 'text-warning-700 dark:text-warning-500'])
                ->columnSpan(2)
                ->money('clp'),

              Tables\Columns\TextColumn::make('abono')
                ->description('Abono')
                ->searchable()
                ->extraAttributes(['class' => 'text-success-700 dark:text-success-500'])
                ->columnSpan(2)
                ->money('clp'),

              Tables\Columns\TextColumn::make('saldo')
                ->description('Saldo')
                ->searchable()
                ->columnSpan(2)
                ->iconPosition('after')
                ->icon(function (Model $record) {
                  if ((int)$record->saldo == 0) {
                    return 'heroicon-o-badge-check';
                  }
                  return null;
                })
                ->extraAttributes(function (Model $record) {
                  if ((int)$record->saldo == 0) {
                    return ['class' => 'text-success-600 dark:text-success-600'];
                  }
                  return [];
                })
                ->weight(function (Model $record) {
                  if ((int)$record->saldo == 0) {
                    return 'bold';
                  }
                  return null;
                })

                ->money('clp'),
            ]),
        ]),
        Panel::make([
          Stack::make([
            Tables\Columns\TextColumn::make('bill.descripcion')
              ->placeholder('FACTURA: Sin detalles')
              ->html(),
            TextColumn::make('descripcion')
              ->html()
              ->placeholder('DESC: Sin detalles'),
            // TextColumn::make('phone'),
          ]),
        ])->collapsible(),

        // Tables\Columns\TextColumn::make('fecha')
        //   ->date(),

        // Tables\Columns\TextColumn::make('work.customer.name'),
        // Tables\Columns\TextColumn::make('work.title'),
        // Tables\Columns\TextColumn::make('cotization.codigo'),
        // Tables\Columns\TextColumn::make('bill.doc'),
        // Tables\Columns\TextColumn::make('total_price'),


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
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->slideOver(),
          Tables\Actions\DeleteAction::make(),
          Tables\Actions\ForceDeleteAction::make(),
          Tables\Actions\RestoreAction::make(),
        ]),
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
