<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipebookResource\Pages;
use App\Filament\Resources\RecipebookResource\RelationManagers;
use App\Models\Recipebook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipebookResource extends Resource
{
    protected static ?string $model = Recipebook::class;

    protected static ?string $navigationLabel = 'Recetas';
    protected static ?string $pluralModelLabel = 'Recetas';
    protected static ?string $navigationGroup = 'Clínica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('consecutive')
                    ->label(__('clinic.recipe_number'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\DatePicker::make('issue_date')
                    ->label(__('clinic.issue_date'))
                    ->nullable(),
                Forms\Components\Select::make('customer_id')
                    ->label(__('clinic.customer'))
                    ->relationship('customer', 'name')
                    ->required(),
                Forms\Components\Select::make('patient_id')
                    ->label(__('clinic.patient'))
                    ->relationship('patient', 'name')
                    ->required(),
                Forms\Components\TextInput::make('diagnosis')
                    ->label(__('clinic.diagnosis'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consecutive')
                    ->label(__('clinic.recipe_number'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label(__('clinic.issue_date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('clinic.created_by'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('clinic.customer'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.name')
                    ->label(__('clinic.patient'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label(__('clinic.diagnosis'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('clinic.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'in_use' => 'danger',
                        'used' => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('clinic.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('clinic.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('clinic.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListRecipebooks::route('/'),
            'create' => Pages\CreateRecipebook::route('/create'),
            'view' => Pages\ViewRecipebook::route('/{record}'),
            'edit' => Pages\EditRecipebook::route('/{record}/edit'),
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
