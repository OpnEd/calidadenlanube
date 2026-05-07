<?php

namespace App\Filament\TenantManager\Resources\DispatchResource\RelationManagers;

use App\Models\PurchaseItem;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('batch_id')
                    ->relationship('central_batch', 'code')
                    ->searchable()
                    ->options(function (Get $get): array {
                        $purchaseItemId = $get('purchase_item_id');

                        if (! $purchaseItemId) {
                            return [];
                        }

                        $productId = PurchaseItem::find($purchaseItemId)?->product_id;

                        if (! $productId) {
                            return [];
                        }

                        return Stock::query()
                            ->where('product_id', $productId)
                            ->where('quantity', '>', 0)
                            ->with('central_batch')
                            ->get()
                            ->pluck('central_batch.code', 'central_batch.id')
                            ->toArray();
                    })
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('quantity')
                    ->minValue(1)
                    ->maxValue(fn (Get $get) => $this->getAvailableStockForSelectedBatch($get))
                    ->helperText(fn (Get $get) => 'Stock disponible: ' . $this->getAvailableStockForSelectedBatch($get) . ' unidades...'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('purchaseItem.product.name')
                    ->label('Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('central_batch.code'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextInputColumn::make('price'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(function ($record) {
                        return number_format(($record->quantity ?? 0) * ($record->price ?? 0), 2);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    ReplicateAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getAvailableStockForSelectedBatch(Get $get): int
    {
        $purchaseItemId = $get('purchase_item_id');
        $centralBatchId = $get('batch_id');

        if (! $purchaseItemId || ! $centralBatchId) {
            return 0;
        }

        $productId = PurchaseItem::find($purchaseItemId)?->product_id;

        if (! $productId) {
            return 0;
        }

        return (int) (Stock::query()
            ->where('product_id', $productId)
            ->where('central_batch_id', $centralBatchId)
            ->value('quantity') ?? 0);
    }
}
