<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\SanitaryRegistryResource\Pages;
use App\Filament\TenantManager\Resources\SanitaryRegistryResource\RelationManagers;
use App\Models\SanitaryRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SanitaryRegistryResource extends Resource
{
    protected static ?string $model = SanitaryRegistry::class;

    protected static ?string $navigationGroup = 'Gestión de productos';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Registros sanitarios';
    protected static ?string $pluralModelLabel = 'Registros sanitarios';
    protected static ?string $modelLabel = 'Registro sanitario';
    protected static ?string $recordTitleAttribute = 'code';
    protected static ?string $slug = 'gestion-de-productos/registros-sanitarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Registro Sanitario')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cum')
                    ->label('CUM')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Registro Sanitario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cum')
                    ->label('CUM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
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
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //
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
            'index' => Pages\ListSanitaryRegistries::route('/'),
            'create' => Pages\CreateSanitaryRegistry::route('/create'),
            'edit' => Pages\EditSanitaryRegistry::route('/{record}/edit'),
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
