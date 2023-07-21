<?php

namespace App\Filament\Resources\Manager\CotizationResource\RelationManagers;

use App\Models\Manager\Bill;
use App\Models\Manager\Cotization;
use App\Models\Manager\Payment;
use DB;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\CreateAction;


use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\View\View;

class PaymentsRelationManager extends RelationManager
{
  protected static string $relationship = 'payment';

  protected static ?string $recordTitleAttribute = 'fecha';

  protected static ?string $modelLabel = 'Pago';

  protected static ?string $pluralModelLabel = 'Pagos';



  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Grid::make(3)->schema([
          Forms\Components\Select::make('manager_cotization_id')
            ->label('Cotizacion')
            ->disabled()
            ->options(Cotization::all()->pluck('codigo', 'id'))
            ->default(function (RelationManager $livewire): int {
              return (int)$livewire->ownerRecord->id;
            }),

          Forms\Components\DatePicker::make('fecha')
            ->default(now())
            ->required(),
          Forms\Components\TextInput::make('doc')
            ->label('Numero dcto.'),
        ]),

        Forms\Components\Grid::make(3)->schema([
          Forms\Components\TextInput::make('total_price')
            ->label('Total:')
            ->hint('Saldo del último pago')
            ->disabled()
            ->required()
            ->reactive()
            ->default(function (RelationManager $livewire, callable $set): string {
              //   return $livewire->ownerRecord->total_price;
              $consulta = DB::table('manager_payments')
                ->where('manager_cotization_id', '=', $livewire->ownerRecord->id)
                ->orderBy('fecha', 'DESC')
                ->get('saldo');
              if (count($consulta) > 0) {
                return (string)$consulta[0]->saldo;
              } else {
                return '0';
              }
            })
            ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0))
            ->afterStateUpdated(function ($state, callable $set, callable $get) {
              $set('saldo', (string)floor((int)$get('total_price') - (int)$get('abono')));
            }),

          Forms\Components\TextInput::make('abono')
            ->label('Abono:')
            ->reactive()
            ->default('0')
            ->afterStateUpdated(function ($state, callable $set, callable $get) {
              $set('saldo', (string)floor((int)$get('total_price') - (int)$get('abono')));
            })
            ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0)),

          Forms\Components\TextInput::make('saldo')
            ->label('Saldo:')
            ->disabled()
            ->default('0')
            ->reactive()
            ->default(function (RelationManager $livewire, callable $set): string {
              //   return $livewire->ownerRecord->total_price;
              $consulta = DB::table('manager_payments')
                ->where('manager_cotization_id', '=', $livewire->ownerRecord->id)
                ->orderBy('fecha', 'DESC')
                ->get('saldo');
              if (count($consulta) > 0) {
                return (string)$consulta[0]->saldo;
              } else {
                return '0';
              }
            })
            ->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: '$', thousandsSeparator: '.', decimalPlaces: 0)),
        ]),

        Forms\Components\Grid::make(2)->schema([
          SpatieMediaLibraryFileUpload::make('file')
            ->label('Adjunto')
            ->preserveFilenames()
            ->enableOpen()
            ->enableDownload(),

          Forms\Components\Checkbox::make('create_bill')
            ->label('Agregar factura?')
            ->helperText('Si está activo se creará un registro de factura con los mismos datos del pago.'),
        ]),
        Forms\Components\RichEditor::make('descripcion')
          ->columnSpan('full')
          ->maxLength(65535),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('cotization.codigo'),
        Tables\Columns\TextColumn::make('fecha'),
        Tables\Columns\TextColumn::make('tipo'),
        Tables\Columns\TextColumn::make('abono')
          ->money('clp'),
        Tables\Columns\TextColumn::make('saldo')
          ->money('clp'),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make()
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->using(function (RelationManager $livewire, array $data): Model {
            $data['user_id'] = auth()->id();
            $data['manager_work_id'] = $livewire->ownerRecord->Work->id;
            $dataBill = [];
            $create_bill = $data['create_bill'];
            unset($data['create_bill']);
            if ($create_bill) {
              $dataBill['manager_cotization_id'] = $data['manager_cotization_id'];
              $dataBill['manager_work_id'] = $data['manager_work_id'];
              $dataBill['customer'] = $livewire->ownerRecord->Work->Customer->name;
              $dataBill['fecha'] = $data['fecha'];
              $dataBill['doc'] = $data['doc'];
              $dataBill['total_price'] = $data['total_price'];
              $dataBill['descripcion'] = $data['descripcion'];
              $dataBill['user_id'] = $data['user_id'];
              $dataBill['tipo'] = 'VENTA';
              $bill = Bill::create($dataBill);
              $data['manager_bill_id'] = $bill->id;
              Notification::make()
                ->title('Factura creada')
                ->success()
                ->send();
            }
            unset($data['abono']);
            unset($data['saldo']);
            return Payment::create($data);
          }),
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
        Tables\Actions\RestoreBulkAction::make(),
        Tables\Actions\ForceDeleteBulkAction::make(),
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
