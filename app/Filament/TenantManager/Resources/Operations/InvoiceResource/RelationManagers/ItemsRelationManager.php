<?php

namespace App\Filament\TenantManager\Resources\Operations\InvoiceResource\RelationManagers;

use App\Models\DispatchItems;
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
    protected static ?string $title = 'Productos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('sale_item_id')
                    ->default(null),
                Forms\Components\Hidden::make('batch_id')
                    ->default(null),
                Forms\Components\Hidden::make('central_batch_id')
                    ->default(null),
                Forms\Components\Select::make('dispatch_item_id')
                    ->label('Producto despachado')
                    ->options(fn (): array => $this->getDispatchItemOptions())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->helperText(
                        fn (): ?string => $this->ownerRecord->dispatch_id
                            ? null
                            : 'Esta factura no tiene un despacho asociado, por lo que no es posible agregar items.'
                    )
                    ->afterStateHydrated(
                        fn (?string $state, Set $set) => $this->fillDispatchItemMetadata(
                            $state ? (int) $state : null,
                            $set,
                            false,
                        )
                    )
                    ->afterStateUpdated(
                        fn (?string $state, Set $set) => $this->fillDispatchItemMetadata(
                            $state ? (int) $state : null,
                            $set,
                        )
                    ),
                Forms\Components\TextInput::make('product_name')
                    ->label('Producto')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('batch_code_preview')
                    ->label('Lote')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('due_date_preview')
                    ->label('Vence')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Hidden::make('due_date'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateTotal($get, $set)),
                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateTotal($get, $set)),
                Forms\Components\TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->prefix('$')
                    ->readOnly()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dispatch_item_id')
            ->columns([
                Tables\Columns\TextColumn::make('dispatchItem.purchaseItem.product.name')
                    ->label('Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('centralBatch.code')
                    ->label('Lote')
                    ->searchable()
                    ->placeholder('Sin lote'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vence')
                    ->date(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => filled($this->ownerRecord->dispatch_id)),
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

    protected function getDispatchItemOptions(): array
    {
        if (! $this->ownerRecord->dispatch_id) {
            return [];
        }

        return DispatchItems::query()
            ->where('dispatch_id', $this->ownerRecord->dispatch_id)
            ->with(['purchaseItem.product', 'central_batch'])
            ->get()
            ->mapWithKeys(fn (DispatchItems $dispatchItem): array => [
                $dispatchItem->getKey() => $this->getDispatchItemLabel($dispatchItem),
            ])
            ->all();
    }

    protected function getDispatchItemLabel(DispatchItems $dispatchItem): string
    {
        $productName = $dispatchItem->purchaseItem?->product?->name
            ?? 'Producto sin nombre';
        $batchCode = $dispatchItem->central_batch?->code ?? 'Sin lote';

        return sprintf(
            '%s | Lote: %s | Cantidad: %s',
            $productName,
            $batchCode,
            $dispatchItem->quantity,
        );
    }

    protected function fillDispatchItemMetadata(?int $dispatchItemId, Set $set, bool $syncAmounts = true): void
    {
        if (! $dispatchItemId) {
            $set('product_name', null);
            $set('batch_code_preview', null);
            $set('due_date_preview', null);
            $set('sale_item_id', null);
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

        $dispatchItem = DispatchItems::query()
            ->with(['purchaseItem.product', 'central_batch'])
            ->find($dispatchItemId);

        $productName = $dispatchItem?->purchaseItem?->product?->name;
        $batch = $dispatchItem?->central_batch;
        $price = (float) ($dispatchItem?->price ?? 0);
        $quantity = (int) ($dispatchItem?->quantity ?? 1);

        $set('product_name', $productName);
        $set('batch_code_preview', $batch?->code);
        $set('due_date_preview', $batch?->expiration_date?->format('Y-m-d H:i'));
        $set('sale_item_id', null);
        $set('batch_id', null);
        $set('central_batch_id', $dispatchItem?->batch_id);
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
