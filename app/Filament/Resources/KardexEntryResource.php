<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KardexEntryResource\Pages;
use App\Filament\Resources\KardexEntryResource\RelationManagers;
use App\Models\KardexEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KardexEntryResource extends Resource
{
    protected static ?string $model = KardexEntry::class;

    protected static ?string $navigationIcon = 'phosphor-microsoft-excel-logo';
    protected static ?string $navigationLabel = 'Kárdex';
    protected static ?string $pluralModelLabel = 'Kárdex';
    protected static ?string $modelLabel = 'Kárdex';
    protected static ?string $navigationGroup = 'Clínica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //Forms\Components\Select::make('inventory_id')
                //    ->relationship('inventory', 'product_name')
                //    ->required(),
                //Forms\Components\Select::make('anesthesia_sheet_id')
                //    ->relationship('anesthesiaSheet', 'code'),
                //Forms\Components\Select::make('anesthesia_sheet_item_id')
                //    ->relationship('anesthesiaSheetItem', 'inventory.product_name'),
                //Forms\Components\Select::make('recipebook_id')
                //    ->relationship('recipebook', 'consecutive'),
                //Forms\Components\DateTimePicker::make('movement_date')
                //    ->required(),
                //Forms\Components\Select::make('movement_type')
                //    ->required()
                //    ->options([
                //        'in' => 'Ingreso',
                //        'out' => 'Egreso',
                //        'adjust' => 'Ajuste',
                //    ])
                //    ->live(),
                //Forms\Components\TextInput::make('quantity')
                //    ->required()
                //    ->numeric(),
                //Forms\Components\TextInput::make('unit')
                //    ->maxLength(20),
                //Forms\Components\TextInput::make('stock_before')
                //    ->disabled()
                //    ->numeric(),
                //Forms\Components\TextInput::make('stock_after')
                //    ->numeric(),
                //Forms\Components\Textarea::make('notes')
                //    ->columnSpanFull(),
                //Forms\Components\Select::make('reference_kardex_entry_id')
                //    ->relationship('reference', 'id')
                //    ->hidden(fn (callable $get) => $get('movement_type') !== 'adjust')
                //    ->numeric(),
                //Forms\Components\TextInput::make('adjustment_reason')
                //    ->hidden(fn (callable $get) => $get('movement_type') !== 'adjust')
                //    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inventory.product_name')
                    ->label(__('clinic.product_name'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('anesthesiaSheet.code')
                    ->label(__('clinic.anesthesia_sheet_code'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipebook.consecutive')
                    ->label(__('clinic.recipebook_consecutive'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_date')
                    ->label(__('clinic.movement_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->label(__('clinic.movement_type'))
                    ->color(fn(string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'primary',
                        'adjust' => 'warning',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('clinic.quantity'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label(__('clinic.unit'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_before')
                    ->label(__('clinic.stock_before'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_after')
                    ->label(__('clinic.stock_after'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_kardex_entry_id')
                    ->label(__('clinic.reference_kardex_entry_id'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('adjustment_reason')
                    ->label(__('clinic.adjustment_reason'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('clinic.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('movement_date')
                    ->label('Rango de fecha')
                    ->form([
                        Forms\Components\DatePicker::make('movement_date_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('movement_date_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['movement_date_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date)
                            )
                            ->when(
                                $data['movement_date_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
            //
            ])
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
            'index' => Pages\ListKardexEntries::route('/'),
            'create' => Pages\CreateKardexEntry::route('/create'),
            'edit' => Pages\EditKardexEntry::route('/{record}/edit'),
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
