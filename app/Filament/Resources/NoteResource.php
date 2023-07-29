<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Filament\Resources\NoteResource\RelationManagers;
use App\Models\Note;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NoteResource extends Resource
{
  protected static ?string $model = Note::class;

  protected static ?string $slug = 'notes';

  protected static ?string $modelLabel = 'Anotacion';

  protected static ?string $pluralModelLabel = 'Anotaciones';

  protected static ?int $navigationSort = 3;


  protected static ?string $recordTitleAttribute = 'titulo';

  //   protected static ?string $navigationGroup = 'Manager';

  protected static ?string $navigationIcon = 'heroicon-o-pencil-alt';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('title')
          ->required()
          ->maxLength(191),
        Forms\Components\RichEditor::make('text')
          ->columnSpan('full'),
      ]);
  }
  protected function getTableContentGrid(): ?array
  {
    return [
      'sm' => 2,
    ];
  }
  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->color(static fn (Model $record): string => $record->color)
          ->weight('bold'),
        Tables\Columns\TextColumn::make('text')
          ->html(),
        Tables\Columns\TextColumn::make('user.name'),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime(),
      ])
      ->defaultSort('created_at', 'desc')
      ->contentGrid([
        'md' => 2,
        'xl' => 3,
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
      'index' => Pages\ManageNotes::route('/'),
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
