<?php

namespace App\Filament\Resources\Manager;

use App\Models\User;
use App\Models\Manager\Cotization;
use App\Models\Manager\Product;
use App\Models\Manager\Customer;
use App\Models\Manager\Work;

use App\Filament\Resources\Manager\CotizationResource\Pages;
use App\Filament\Resources\Manager\CotizationResource\RelationManagers;
use App\Filament\Resources\Manager\CotizationResource\Widgets\CotizationStats;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;


use Filament\Forms\Components\TextInput\Mask;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Integer as TypesInteger;
use Ramsey\Uuid\Type\Integer;
use Squire\Models\Currency;

class CotizationResource extends Resource
{
  protected static ?string $model = Cotization::class;

  protected static ?int $navigationSort = 3;

  protected static ?string $slug = 'manager/cotizations';

  protected static ?string $modelLabel = 'Cotizacion';

  protected static ?string $pluralModelLabel = 'Cotizaciones';

  protected static ?string $recordTitleAttribute = 'codigo';

  protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-document-text';


  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Group::make()
          ->schema([
            Forms\Components\Card::make()
              ->schema(static::getFormSchema())
              ->columns(1),
            Forms\Components\Section::make('Items')
              ->schema(static::getFormSchema('repetidor')),
          ])
          ->columnSpan(4),

        Forms\Components\Card::make()
          ->columns(4)
          ->columnSpan(4)
          ->hidden(fn (?Cotization $record) => $record === null)
          ->schema([
            Forms\Components\Placeholder::make('created_at')
              ->label('Creado el')
              ->content(fn (Cotization $record): ?string => $record->created_at?->diffForHumans() . ' (' . $record->created_at->format('H:i d-m-Y') . ')')
              ->columnSpan(['lg' => 2]),
            Forms\Components\Placeholder::make('updated_at')
              ->label('Última modificación')
              ->content(fn (Cotization $record): ?string => $record->updated_at?->diffForHumans() . ' (' . $record->updated_at->format('H:i d-m-Y') . ')')
              ->columnSpan(['lg' => 2]),
          ]),
      ])
      ->columns(4);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([

        Tables\Columns\TextColumn::make('codigo')
          ->size('sm')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('fecha')
          ->date()
          ->extraAttributes(function (?Model $record) {
            $fecha = Carbon::parse($record->fecha);
            $hoy = Carbon::parse(now());
            return $fecha->add((int)$record->validez, 'day') <= $hoy
              ? ['class' => 'text-warning-600']
              : ['class' => 'text-primary-600'];
          })
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('vencimiento')
          ->toggleable(isToggledHiddenByDefault: true)
          ->date()
          ->toggleable()
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('validez')
          ->toggleable(isToggledHiddenByDefault: true)
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('work.title')
          ->words(3)
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('work.customer.name')
          ->words(2)
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('user.name')
          ->toggleable(isToggledHiddenByDefault: true)
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('total_price')
          ->money('clp')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('payments_sum')
          ->label('Pagos')
          ->placeholder('S/P')
          ->sum('payments', 'abono')
          ->sortable(),

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
      ->defaultSort('created_at', 'desc')

      ->filters([

        Tables\Filters\TrashedFilter::make(),
        Tables\Filters\Filter::make('created_at')
          ->form([
            Forms\Components\DatePicker::make('created_from')
              ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
            Forms\Components\DatePicker::make('created_until')
              ->placeholder(fn ($state): string => now()->format('M d, Y')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
              )
              ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
              );
          })
          ->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['created_from'] ?? null) {
              $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
            }
            if ($data['created_until'] ?? null) {
              $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
            }

            return $indicators;
          }),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
        Tables\Actions\Action::make('pdf')
          ->label('Descargar PDF')
          ->color('success')
          ->icon('heroicon-s-download')
          ->action(function (Model $record) {
            return response()->streamDownload(function () use ($record) {
              echo Pdf::loadHtml(
                Blade::render('pdf', ['record' => $record])
              )->stream();
            }, $record->codigo . '_' . $record->Work->Customer->name . '_' . $record->Work->name . '.pdf');
          })
          ->openUrlInNewTab(),
        // Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),

        ])
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
      //   RelationManagers\PaymentsRelationManager::class,
    ];
  }

  public static function getWidgets(): array
  {
    return [
      CotizationStats::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListCotizations::route('/'),
      'create' => Pages\CreateCotization::route('/create'),
      'edit' => Pages\EditCotization::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
  }

  public static function getGloballySearchableAttributes(): array
  {
    return ['codigo'];
  }

  public static function getGlobalSearchResultDetails(Model $record): array
  {
    return [
      'Customer' => $record->customer->name,
      'Fecha' => $record->fecha . ' +' . $record->validez . 'd',
    ];
  }

  protected static function getGlobalSearchEloquentQuery(): Builder
  {
    return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
  }

    protected static function getNavigationBadge(): ?string
    {
      return static::getModel()::where('vencimiento', '>=', now())->count();
    }

  public static function getFormSchema(?string $section = null): array
  {
    if ($section === 'repetidor') {
      return [

        Forms\Components\Repeater::make('items')
          ->relationship('items')
          ->collapsible()
          ->schema([

            Grid::make(12)
              ->schema([
                Forms\Components\Select::make('manager_product_id')
                  ->label('Producto')
                  ->options(Product::query()->pluck('nombre', 'id'))
                  ->createOptionForm([
                    Forms\Components\TextInput::make('nombre')
                      ->default('a' . \random_int(23, 99))
                      ->required(),
                    Forms\Components\TextInput::make('precio_stock')
                      ->required()
                      ->default(12)
                      ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0)),
                  ])
                  ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                    return $action
                      ->modalHeading('Crear producto')
                      ->modalButton('Crear')
                      ->modalWidth('md');
                  })
                  ->createOptionUsing(function (array $data) {
                    if ($product = Product::create($data)) {
                      return $product->id;
                    }
                  })
                  ->required()
                  ->searchable()
                  ->reactive()
                  ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state) {
                      $set('precio_stock', strval(Product::find($state)->precio_stock));
                      $set('precio_anotado', strval(Product::find($state)->precio_stock));
                      $set('total', strval((int)Product::find($state)->precio_stock * (int)$get('cantidad')));
                      $repeaters = $get('../../items');
                      $total = 0;
                      foreach ($repeaters as $repeater) {
                        if (array_key_exists('cantidad', $repeater)) {
                          $total += (int)$repeater['precio_anotado'] * (int)$repeater['cantidad'];
                        }
                      }
                      $set('../../virtual_total_price', strval($total));
                    } else {
                      $set('precio_stock', null);
                      $set('precio_anotado', null);
                      $set('cantidad', null);
                      $set('total', null);
                    }
                  })
                  ->columnSpan(5),

                Forms\Components\TextInput::make('precio_stock')
                  ->label('Precio stock')
                  ->disabled()
                  ->numeric()
                  ->reactive()
                  ->default('0')
                  //   ->mask(fn (Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
                  ->columnSpan(2),

                Forms\Components\TextInput::make('precio_anotado')
                  ->label('Precio anotado')
                  ->numeric()
                  ->required()
                  ->reactive()
                  ->default('0')
                  ->afterStateUpdated(function ($get, $set, $state) {
                    $set('total', (int)$get('precio_anotado') * (int)$get('cantidad'));
                    $repeaters = $get('../../items');
                    $total = 0;
                    foreach ($repeaters as $repeater) {
                      if (array_key_exists('cantidad', $repeater)) {
                        $total += (int)$repeater['precio_anotado'] * (int)$repeater['cantidad'];
                      }
                    }
                    $set('../../virtual_total_price', strval($total));
                  })
                  //   ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
                  ->columnSpan(2),

                Forms\Components\TextInput::make('cantidad')
                  ->numeric()
                  ->default(1)
                  ->required()
                  ->reactive()
                  ->rules(['integer', 'min:0'])
                  ->afterStateUpdated(function ($get, $set, $state) {
                    $set('total', (int)$get('precio_anotado') * (int)$get('cantidad'));
                    $repeaters = $get('../../items');
                    $total = 0;
                    foreach ($repeaters as $repeater) {
                      if (array_key_exists('cantidad', $repeater)) {
                        $total += (int)$repeater['precio_anotado'] * (int)$repeater['cantidad'];
                      }
                    }
                    $set('../../virtual_total_price', strval($total));
                  })
                  ->columnSpan(1),

                Forms\Components\Placeholder::make('total')
                  ->content(fn (Closure $get) => '$ ' . \number_format(((int)$get('precio_anotado') * (int)$get('cantidad')), 0, ',', '.'))
                  ->columnSpan(2),
              ])
          ])
          ->itemLabel(function (array $state) {
            if (array_key_exists('manager_product_id', $state)) {
              if ($prod = Product::firstWhere('id', $state['manager_product_id'])) {
                return $state['cantidad'] . ' x ' . $prod->nombre;
              }
            }
          })
          ->orderable()
          ->defaultItems(1)
          ->cloneable(),

        Forms\Components\Fieldset::make('Resumen')
          ->schema([
            Forms\Components\Placeholder::make('virtual_neto')
              ->label(false)
              ->extraAttributes(['class' => 'text-xl text-center'])
              ->columnSpan(1)
              ->content(function (callable $get, callable $set, $state) {
                $repeaters = $get('items');
                $neto = 0;
                foreach ($repeaters as $repeater) {
                  if (array_key_exists('cantidad', $repeater)) {
                    $neto += (int)$repeater['precio_anotado'] * (int)$repeater['cantidad'];
                  }
                }
                $iva = floor($neto * 0.19);
                $total = floor($neto * 1.19);
                $set('iva_price', $iva);
                $set('total_price', $total);
                return 'Neto: $ ' . number_format(strval($neto), 0, '', '.');
              }),
            Forms\Components\Placeholder::make('virtual_iva')
              ->label(false)
              ->extraAttributes(['class' => 'text-xl text-center'])
              ->columnSpan(1)
              ->content(function (callable $get, callable $set, $state) {
                $iva = $get('iva_price');
                return 'IVA (19%): $ ' . number_format(strval($iva), 0, '', '.');
              }),
            Forms\Components\Placeholder::make('virtual_total_price')
              ->label(false)
              ->extraAttributes(['class' => 'text-xl text-center'])
              ->columnSpan(1)
              ->content(function (callable $get, callable $set, $state) {
                $total = $get('total_price');
                return 'Total: $ ' . number_format(strval($total), 0, '', '.');
              }),

            Forms\Components\Hidden::make('iva_price'),
            Forms\Components\Hidden::make('total_price'),
          ])
          ->columns(3),

      ];
    }

    return [

      Forms\Components\Select::make('manager_work_id')
        ->relationship('work', 'title')
        ->options(Work::query()->pluck('title', 'id'))
        ->searchable()
        ->required()
        ->reactive()
        ->createOptionForm([
          Forms\Components\TextInput::make('title')
            ->required(),
          Forms\Components\Select::make('manager_customer_id')
            ->relationship('customer', 'name')
            ->options(Customer::query()->pluck('name', 'id'))
            ->searchable()
            ->required()
            ->reactive()
            ->columnSpan('full'),
          Forms\Components\DatePicker::make('inicio'),
        //   Forms\Components\TextInput::make('descripcion'),

        ])
        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
          return $action
            ->modalHeading('Crear trbajo')
            ->modalButton('Crear trbajo')
            ->modalWidth('xl');
        })
        ->createOptionUsing(function (array $data) {
          if ($work = Work::create($data)) {
            return $work->id;
          }
        })
        ->afterStateUpdated(function ($state, callable $set) {
          $set('customer', Work::find($state)->customer->name);
        })
        ->columnSpan('full'),

      Forms\Components\Grid::make(3)->schema([

        Forms\Components\TextInput::make('codigo')
          ->default('COT' . date("dmy") . str_pad(DB::table('manager_cotizations')->latest('id')->first()?->id ?? 0, 3, '0', STR_PAD_LEFT))
          ->disabled()
          ->required(),

      ]),


      Forms\Components\Grid::make(3)->schema([
        Forms\Components\DatePicker::make('fecha')
          ->default(now())
          ->displayFormat('d M, Y')
          ->timezone('America/Santiago')
          ->reactive()
          ->afterStateUpdated(function(Closure $get, Closure $set, $state){
            $fecha = Carbon::parse($state);
            $validez = $get('validez');
            $vencimiento = $fecha->addDays((int)$validez);
            $set('vencimiento', $vencimiento);
          }),


        Forms\Components\TextInput::make('validez')
          ->default(5)
          ->postfix('días')
          ->required()
          ->reactive()
          ->afterStateUpdated(function(Closure $get, Closure $set, $state){
            $fecha = Carbon::parse($get('fecha'));
            $validez = $state;
            $vencimiento = $fecha->addDays((int)$validez);
            $set('vencimiento', $vencimiento);
          }),

        Forms\Components\DatePicker::make('vencimiento')
        ->visibleOn('edit')
        ->reactive()
        ->disabled()
          ->timezone('America/Santiago'),

      ]),

      Forms\Components\RichEditor::make('descripcion')
        ->columnSpan('full')
        ->maxLength(65535),
    ];
  }
}
