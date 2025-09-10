<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\CentralBatchResource\Pages;
use App\Filament\TenantManager\Resources\CentralBatchResource\RelationManagers;
use App\Models\CentralBatch;
use App\Models\SanitaryRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CentralBatchResource extends Resource
{
    protected static ?string $model = CentralBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sanitary_registry_id')
                    ->options(SanitaryRegistry::all()->pluck('code', 'id'))
                    ->required(),
                Forms\Components\Select::make('manufacturer_id')
                    ->relationship('manufacturer', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('manufacturing_date')
                    ->required(),
                Forms\Components\DatePicker::make('expiration_date')
                    ->required(),
                Forms\Components\KeyValue::make('data')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sanitary_registry.code')
                    ->sortable(),
                Tables\Columns\TextColumn::make('manufacturer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('manufacturing_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            'index' => Pages\ListCentralBatches::route('/'),
            'create' => Pages\CreateCentralBatch::route('/create'),
            'edit' => Pages\EditCentralBatch::route('/{record}/edit'),
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
