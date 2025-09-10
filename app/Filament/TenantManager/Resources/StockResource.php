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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
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
                                ->label('Manufacturer')
                                ->options(
                                    \App\Models\Manufacturer::all()->pluck('name', 'id')->toArray()
                                )
                                ->required(),
                            Forms\Components\Select::make('sanitary_registry_id')
                                ->label('Sanitary Registry')
                                ->options(
                                    \App\Models\SanitaryRegistry::all()->mapWithKeys(function ($registry) {
                                        return [$registry->id => "{$registry->code} ({$registry->product_name})"];
                                    })->toArray()
                                )
                                ->required(),
                            Forms\Components\DatePicker::make('manufacturing_date')
                                ->label('Manufacturing Date')
                                ->required(),
                            Forms\Components\DatePicker::make('expiration_date')
                                ->label('Expiration Date')
                                ->required(),
                            Forms\Components\TextInput::make('code')
                                ->label('Batch Code')
                                ->required(),
                            Forms\Components\KeyValue::make('data')
                                ->label('Additional Data')
                                ->columnSpanFull()
                        ]
                    )
                    ->searchable()  // mejor UX si hay muchos lotes
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('purchase_price')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('central_batch.code')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
