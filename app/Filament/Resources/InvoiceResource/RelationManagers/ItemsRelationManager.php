<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\SaleItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('dispatch_item_id')
                    ->default(null),
                Forms\Components\Hidden::make('central_batch_id')
                    ->default(null),
                Forms\Components\Select::make('sale_item_id')
                    ->label('Sold product')
                    ->options(fn (): array => $this->getSaleItemOptions())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->helperText(
                        fn (): ?string => $this->ownerRecord->sale_id
                            ? null
                            : 'This invoice has no related sale, so items cannot be added.'
                    )
                    ->afterStateHydrated(
                        fn (?string $state, Set $set) => $this->fillSaleItemMetadata(
                            $state ? (int) $state : null,
                            $set,
                            false,
                        )
                    )
                    ->afterStateUpdated(
                        fn (?string $state, Set $set) => $this->fillSaleItemMetadata(
                            $state ? (int) $state : null,
                            $set,
                        )
                    ),
                Forms\Components\TextInput::make('product_name')
                    ->label('Product')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('batch_code_preview')
                    ->label('Batch')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('due_date_preview')
                    ->label('Due date')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Hidden::make('batch_id'),
                Forms\Components\Hidden::make('due_date'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateTotal($get, $set)),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateTotal($get, $set)),
                Forms\Components\TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->readOnly()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sale_item_id')
            ->columns([
                Tables\Columns\TextColumn::make('saleItem.inventory.product.name')
                    ->label(__('Product Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch.code')
                    ->label(__('Batch Code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Issued Date'))
                    ->date(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Quantity')),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Sale Price'))
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => filled($this->ownerRecord->sale_id)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getSaleItemOptions(): array
    {
        if (! $this->ownerRecord->sale_id) {
            return [];
        }

        return SaleItem::query()
            ->where('sale_id', $this->ownerRecord->sale_id)
            ->with(['inventory.product', 'inventory.batch'])
            ->get()
            ->mapWithKeys(fn (SaleItem $saleItem): array => [
                $saleItem->getKey() => $this->getSaleItemLabel($saleItem),
            ])
            ->all();
    }

    protected function getSaleItemLabel(SaleItem $saleItem): string
    {
        $productName = $saleItem->inventory?->product?->name
            ?? $saleItem->inventory?->product_name
            ?? 'Unnamed product';
        $batchCode = $saleItem->inventory?->batch?->code ?? 'No batch';

        return sprintf(
            '%s | Batch: %s | Quantity: %s',
            $productName,
            $batchCode,
            $saleItem->quantity,
        );
    }

    protected function fillSaleItemMetadata(?int $saleItemId, Set $set, bool $syncAmounts = true): void
    {
        if (! $saleItemId) {
            $set('product_name', null);
            $set('batch_code_preview', null);
            $set('due_date_preview', null);
            $set('batch_id', null);
            $set('central_batch_id', null);
            $set('dispatch_item_id', null);
            $set('due_date', null);

            if ($syncAmounts) {
                $set('quantity', 1);
                $set('price', 0);
                $set('total', 0);
            }

            return;
        }

        $saleItem = SaleItem::query()
            ->with(['inventory.product', 'inventory.batch'])
            ->find($saleItemId);

        $productName = $saleItem?->inventory?->product?->name
            ?? $saleItem?->inventory?->product_name;
        $batch = $saleItem?->inventory?->batch;
        $price = (float) ($saleItem?->sale_price ?? 0);
        $quantity = (int) ($saleItem?->quantity ?? 1);

        $set('product_name', $productName);
        $set('batch_code_preview', $batch?->code);
        $set('due_date_preview', $batch?->expiration_date?->format('Y-m-d H:i'));
        $set('batch_id', $saleItem?->inventory?->batch_id);
        $set('central_batch_id', null);
        $set('dispatch_item_id', null);
        $set('due_date', $batch?->expiration_date?->format('Y-m-d H:i:s'));

        if (! $syncAmounts) {
            return;
        }

        $set('quantity', $quantity);
        $set('price', $price);
        $set('total', round($quantity * $price, 2));
    }

    protected function updateTotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('price') ?? 0);

        $set('total', round($quantity * $price, 2));
    }
}
