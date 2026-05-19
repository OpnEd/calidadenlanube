<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\PharmaceuticalFormResource\Pages;
use App\Filament\TenantManager\Resources\PharmaceuticalFormResource\RelationManagers;
use App\Models\PharmaceuticalForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PharmaceuticalFormResource extends Resource
{
    protected static ?string $model = PharmaceuticalForm::class;

    protected static ?string $navigationGroup = 'Gestión de productos';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Formas farmacéuticas';
    protected static ?string $pluralModelLabel = 'Formas farmacéuticas';
    protected static ?string $modelLabel = 'Forma farmacéutica';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $slug = 'gestion-de-productos/formas-farmaceuticas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->label('Descripción')
                    ->required()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ], position: ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListPharmaceuticalForms::route('/'),
            //'create' => Pages\CreatePharmaceuticalForm::route('/create'),
            //'view' => Pages\ViewPharmaceuticalForm::route('/{record}'),
            //'edit' => Pages\EditPharmaceuticalForm::route('/{record}/edit'),
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
