<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\DispatchResource\Pages;
use App\Filament\TenantManager\Resources\DispatchResource\RelationManagers;
use App\Models\Dispatch;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Enums\ActionsPosition;

class DispatchResource extends Resource
{
    protected static ?string $model = Dispatch::class;

    protected static ?string $navigationGroup = 'Operaciones externas';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Despachos';
    protected static ?string $pluralModelLabel = 'Despachos';
    protected static ?string $modelLabel = 'Despacho';
    protected static ?string $slug = 'operaciones-externas/despachos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles del despacho')
                    ->schema([
                        Forms\Components\Select::make('purchase_id')
                            ->relationship(
                                name: 'purchase',
                                titleAttribute: 'code')
                            ->getOptionLabelFromRecordUsing(fn (Purchase $record): string => "{$record->code} - " . ($record->team?->name ?? 'Sin Cliente'))
                            ->required(),
                        Forms\Components\Select::make('team_id')
                            ->relationship(
                                name: 'team',
                                titleAttribute: 'name')
                            ->required(),
                        Forms\Components\DateTimePicker::make('dispatched_at'),
                    ])
                    ->columns(3)
                    ->collapsed(),
                Section::make('Metadatos del despacho')
                    ->schema([
                        Forms\Components\KeyValue::make('data'),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase.code')
                    ->label('Código Orden de compra')
                    ->sortable(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Procesado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dispatched_at')
                    ->label('Fecha de despacho')
                    ->dateTime()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDispatches::route('/'),
            'create' => Pages\CreateDispatch::route('/create'),
            'view' => Pages\ViewDispatch::route('/{record}'),
            'edit' => Pages\EditDispatch::route('/{record}/edit'),
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
