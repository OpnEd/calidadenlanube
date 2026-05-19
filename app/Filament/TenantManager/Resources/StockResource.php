<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\StockResource\Pages;
use App\Filament\TenantManager\Resources\StockResource\RelationManagers;
use App\Models\Batch;
use App\Models\CentralBatch;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationGroup = 'Operaciones internas';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Inventarios';
    protected static ?string $pluralModelLabel = 'Inventarios';
    protected static ?string $modelLabel = 'Inventario';
    protected static ?string $slug = 'operaciones-internas/inventarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->searchable()
                    ->options(
                        Product::all()->mapWithKeys(function ($product) {
                            return [$product->id => "{$product->name} ({$product->description})"];
                        })->toArray()
                    )
                    ->required(),
                Forms\Components\Select::make('batch_id')
                    ->relationship('central_batch', 'code')
                    ->createOptionForm(
                        [
                            Forms\Components\Select::make('manufacturer_id')
                                ->label('Fabricante')
                                ->options(
                                    \App\Models\Manufacturer::all()->pluck('name', 'id')->toArray()
                                )
                                ->required(),
                            Forms\Components\Select::make('sanitary_registry_id')
                                ->label('Registro Sanitario')
                                ->options(
                                    \App\Models\SanitaryRegistry::all()->mapWithKeys(function ($registry) {
                                        return [$registry->id => "{$registry->code} ({$registry->product_name})"];
                                    })->toArray()
                                )
                                ->required(),
                            Forms\Components\DatePicker::make('manufacturing_date')
                                ->label('Fecha de Fabricación')
                                ->required(),
                            Forms\Components\DatePicker::make('expiration_date')
                                ->label('Fecha de Expiración')
                                ->required(),
                            Forms\Components\TextInput::make('code')
                                ->label('Código del Lote')
                                ->required(),
                            Forms\Components\KeyValue::make('data')
                                ->label('Datos Adicionales')
                                ->columnSpanFull()
                        ]
                    )
                    ->searchable()  // mejor UX si hay muchos lotes
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('purchase_price')
                    ->label('Precio de Compra')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable(),
                Tables\Columns\TextColumn::make('central_batch.code')
                    ->label('Código del Lote')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Precio de Compra')
                    ->numeric()
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
