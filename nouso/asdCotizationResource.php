<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\CotizationResource\Pages;
use App\Filament\Resources\Manager\CotizationResource\RelationManagers;
use App\Models\Manager\Product;
use App\Models\Manager\Cotization;
use App\Models\Manager\Customer;
use App\Models\Manager\Work;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Squire\Models\Currency;




class CotizationResource extends Resource
{
    protected static ?string $model = Cotization::class;

    protected static ?string $slug = 'manager/cotizations';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Manager';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Card::make()
                            ->schema(static::getFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Items')
                            ->schema(static::getFormSchema('items')),
                    ])
                    ->columnSpan(['md' => 12]),

                Forms\Components\Card::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Creado:')
                            ->content(fn (Cotization $record): ?string => $record->created_at?->diffForHumans())
                            ->columnSpan(12),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Modificado:')
                            ->content(fn (Cotization $record): ?string => $record->updated_at?->diffForHumans())
                            ->columnSpan(12),
                    ])
                    // ->columnSpan(['md' => 12])
                    ->hidden(fn (?Cotization $record) => $record === null),
            ])
            ->columns(12);

        // return $form
        //     ->schema([
        //         Forms\Components\DatePicker::make('fecha'),
        //         Forms\Components\TextInput::make('empresa')
        //             ->maxLength(191),
        //         Forms\Components\TextInput::make('atencion')
        //             ->maxLength(191),
        //         Forms\Components\TextInput::make('validez')
        //             ->required(),
        //         Forms\Components\Toggle::make('contabilizar')
        //             ->required(),
        //         Forms\Components\Textarea::make('descripcion')
        //             ->maxLength(65535),
        //         Forms\Components\Select::make('user_id')
        //             ->relationship('user', 'name'),
        //         Forms\Components\Select::make('work_id')
        //             ->relationship('work', 'id'),
        //     ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('empresa'),
                Tables\Columns\TextColumn::make('atencion'),
                Tables\Columns\TextColumn::make('validez'),
                Tables\Columns\IconColumn::make('contabilizar')
                    ->boolean(),
                Tables\Columns\TextColumn::make('descripcion'),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('manager_customers.name'),
                Tables\Columns\TextColumn::make('manager_work.nombre'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime(),
                // Tables\Columns\TextColumn::make('deleted_at')
                //     ->dateTime(),
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
            'index' => Pages\ListCotizations::route('/'),
            'create' => Pages\CreateCotization::route('/create'),
            'view' => Pages\ViewCotization::route('/{record}'),
            'edit' => Pages\EditCotization::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getFormSchema(?string $section = null): array
    {
        if ($section === 'items') {
            return [
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->grid(1)
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('manager_product_id')
                            ->relationship('manager_products', 'nombre')
                            ->label('Producto')
                            ->options(Product::query()->pluck('nombre', 'id'))
                            ->required()
                            ->searchable()
                            // ->reactive()
                            // ->afterStateUpdated(function(callable $get, callable $set, $state){
                            //     $set('precio', Product::find($state)?->precio ?? 0);
                            //     $set('precio_anotado', Product::find($state)?->precio ?? 0);
                            //     $set('total', Product::find($state)?->precio*$get('cantidad') ?? 0);
                            // })
                            ->columnSpan(span: 'full'),

                        Forms\Components\TextInput::make('precio')
                            ->label('Precio unitario')
                            ->disabled()
                            ->numeric()
                            ->required()
                            ->columnSpan([
                                'md' => 1,
                            ]),
                            //  ->afterStateUpdated(function (callable $get, callable $set, $state) {
                            //     $set('precio_anotado', $get('precio'));
                            // }),
                            // ->saveRelationshipsWhenHidden(),

                        Forms\Components\TextInput::make('precio_anotado')
                            ->label('Precio unitario')
                            // ->disabled()
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                return $set('total', $get('cantidad')*$get('precio_anotado'));
                            })
                            ->columnSpan([
                                'md' => 1,
                            ]),

                        Forms\Components\TextInput::make('cantidad')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                return $set('total', $get('cantidad')*$get('precio_anotado'));
                            })
                            ->columnSpan([
                                'md' => 1,
                            ]),

                        Forms\Components\Placeholder::make('total')
                            ->content(function ($get) {
                                return $get('precio')*$get('cantidad');
                                // Use $get('products') to get an array of items.
                                // Loop through each item and make a total
                                // Return the total from this function
                            }),
                    ])
                    ->orderable()
                    ->required()
                    ->defaultItems(count: 1)
                    ->disableLabel()
                    ->columns([
                        'md' => 3,
                    ]),
            ];
        }

        return [
            Forms\Components\DatePicker::make('fecha'),
            Forms\Components\TextInput::make('empresa')
                ->maxLength(191),
            Forms\Components\TextInput::make('atencion')
                ->maxLength(191),
            Forms\Components\TextInput::make('validez')
                ->required(),
            Forms\Components\Toggle::make('contabilizar')
                ->required(),
            Forms\Components\Textarea::make('descripcion')
                ->columnSpan(2)
                ->maxLength(65535),
            Forms\Components\Hidden::make('user_id'),
            // Forms\Components\Select::make('manager_work_id')
            //     ->relationship('manager_work', 'id'),

            // Forms\Components\TextInput::make('number')
            //     ->default('OR-' . random_int(100000, 999999))
            //     ->disabled()
            //     ->required(),

            // Forms\Components\Select::make('manager_customer_id')
            //     ->relationship('customer', 'name')
            //     ->searchable()
            //     ->required()
            //     ->createOptionForm([
            //         Forms\Components\TextInput::make('name')
            //             ->required(),

            //         Forms\Components\TextInput::make('email')
            //             ->required()
            //             ->email()
            //             ->unique(),

            //         Forms\Components\TextInput::make('phone'),

            //         Forms\Components\Select::make('gender')
            //             ->placeholder('Select gender')
            //             ->options([
            //                 'male' => 'Male',
            //                 'female' => 'Female',
            //             ])
            //             ->required(),
            //     ])
            //     ->createOptionAction(function (Forms\Components\Actions\Action $action) {
            //         return $action
            //             ->modalHeading('Create customer')
            //             ->modalButton('Create customer')
            //             ->modalWidth('lg');
            //     }),

            // Forms\Components\Select::make('status')
            //     ->options([
            //         'new' => 'New',
            //         'processing' => 'Processing',
            //         'shipped' => 'Shipped',
            //         'delivered' => 'Delivered',
            //         'cancelled' => 'Cancelled',
            //     ])
            //     ->required(),

            // Forms\Components\Select::make('currency')
            //     ->searchable()
            //     ->getSearchResultsUsing(fn (string $query) => Currency::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
            //     ->getOptionLabelUsing(fn ($value): ?string => Currency::find($value)?->getAttribute('name'))
            //     ->required(),


            // Forms\Components\MarkdownEditor::make('notes')
            //     ->columnSpan('full'),
        ];
    }
}
