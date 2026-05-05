<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationGroup = 'Transacciones';
    protected static ?string $navigationLabel = 'Inventario';
    protected static ?string $modelLabel = 'Existencia';
    protected static ?string $pluralModelLabel = 'Existencias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = \App\Models\Product::find($state);
                            if ($product) {
                                $set('product_name', $product->name);
                            }
                        } else {
                            $set('product_name', null);
                        }
                    }),

                Forms\Components\TextInput::make('product_name')
                    ->label('Nombre del producto')
                    ->required()
                    ->maxLength(255)
                    ->reactive(), // <- importante para que se actualice

                Forms\Components\Select::make('batch_id')
                    ->label('Lote')
                    ->relationship(
                        'batch',
                        'code',
                        fn (Builder $query) => $query->where('team_id', Filament::getTenant()?->id)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad comercial')
                    ->helperText('Para productos fraccionables se convierte automaticamente a unidades minimas en inventario.')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('purchase_price')
                    ->label('Precio de compra')
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
                Tables\Columns\TextColumn::make('batch.code')
                    ->label('Lote')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Precio de compra')
                    ->numeric()
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nombre del producto')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Editar'),
                ]),
            ])
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
