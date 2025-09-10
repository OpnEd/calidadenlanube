<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Inventory;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Redirect;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Gate;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickSale')
                ->label('Iniciar venta!')
                ->icon('phosphor-shopping-bag')
                ->modalHeading('Nueva Venta')
                ->form([
                    Forms\Components\Select::make('inventory_id')
                        ->label('Inventario / Producto (Lote)')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            // Construimos una query base sobre Inventory
                            $query = Inventory::query()->with(['batch', 'product']); // intentamos eager load de relaciones comunes

                            // Añadimos condiciones de búsqueda en forma segura:
                            $query->where(function ($q) use ($search) {
                                // Si existe la relación batch->product, whereHas no fallará si la relación no existe,
                                // pero no podemos llamar whereHas('batch.product') si batch->product no existe como método.
                                // Por seguridad, chequeamos la existencia del método sobre una instancia.
                                $inv = new Inventory();

                                // 1) Si Inventory tiene relación product()
                                if (method_exists($inv, 'product')) {
                                    $q->whereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                                }

                                // 2) Si Inventory tiene relación batch(), y Batch tiene relación product()
                                if (method_exists($inv, 'batch')) {
                                    $batch = new \App\Models\Batch();
                                    if (method_exists($batch, 'product')) {
                                        $q->orWhereHas('batch.product', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
                                    } else {
                                        // si batch no tiene product, podemos buscar por batch.code (si existe)
                                        $q->orWhereHas('batch', fn($q4) => $q4->where('code', 'like', "%{$search}%"));
                                    }
                                }

                                // 3) Fallback: buscar por un campo en inventories (ej. product_name) si existe
                                // Usamos Schema::hasColumn para evitar errores si la columna no existe
                                if (\Illuminate\Support\Facades\Schema::hasColumn('inventories', 'product_name')) {
                                    $q->orWhere('product_name', 'like', "%{$search}%");
                                }

                                // 4) Otra búsqueda por barcode u otro campo si existe
                                if (\Illuminate\Support\Facades\Schema::hasColumn('inventories', 'bar_code')) {
                                    $q->orWhere('bar_code', 'like', "%{$search}%");
                                }
                            });

                            $rows = $query->limit(50)->get();

                            return $rows->mapWithKeys(function ($inv) {
                                // Construcción robusta de la etiqueta:
                                $productName = null;
                                if (isset($inv->batch) && method_exists($inv->batch, 'product') && $inv->batch->product) {
                                    $productName = $inv->batch->product->name;
                                } elseif (isset($inv->product) && $inv->product) {
                                    $productName = $inv->product->name;
                                } elseif (!empty($inv->product_name)) {
                                    $productName = $inv->product_name;
                                } else {
                                    $productName = '—';
                                }

                                $batchCode = $inv->batch->code ?? ($inv->batch_id ?? null);

                                $label = $productName . ($batchCode ? " • Lote: {$batchCode}" : '');

                                return [$inv->id => $label];
                            })->toArray();
                        })
                        ->searchable()
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            // Obtener el inventory seleccionado
                            $inventory = $get('inventory_id') ? \App\Models\Inventory::find($get('inventory_id')) : null;

                            // Calcular el sale_price usando la relación correcta
                            $price = $inventory?->product?->peripheralPrice?->sale_price ?? 0;

                            $set('sale_price', $price);
                            $set('total', ($get('quantity') ?? 0) * $price);

                            // Establecer el nombre del producto directamente desde inventories
                            $productName = $inventory?->product_name ?? '';
                            $set('product_name', $productName);
                        })
                        ->required()
                        ->live()
                        ->helperText(
                            fn(Get $get) => 'Stock disponible: '
                                . (
                                    ($inventoryId = $get('inventory_id'))
                                    ? Inventory::where('product_id', Inventory::find($inventoryId)?->product_id)
                                    ->sum('quantity')
                                    : 0
                                ) . ' unidades...'
                        ),
                    Forms\Components\TextInput::make('product_name')
                        ->readOnly(),
                    Forms\Components\TextInput::make('sale_price')
                        ->readOnly(),
                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            // Calcular y persistir total
                            $salePrice = $get('sale_price') ?? 0;
                            $set('total', $state * $salePrice);
                        }),
                    Forms\Components\Hidden::make('total')
                        ->default(0),
                ])
                ->action(function (array $data, Action $action) {
                    // Validar que exista el producto
                    if (empty($data['inventory_id'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Debes seleccionar un producto')
                            ->color('danger')
                            ->send();
                        return;
                    }

                    // Crear la venta
                    $sale = Sale::create([
                        'team_id'       => Filament::getTenant()->id,
                        'customer_id'   => 1,
                        'user_id'       => Auth::user()->id,
                        'total'         => $data['total'] ?? 0,
                        'status'        => 'in-progress',
                        'code'          => (new Sale())->generateCode(),
                        'data'          => [],
                    ]);

                    // Adjuntar el producto como item de la venta
                    $sale->items()->create([
                        'inventory_id'   => $data['inventory_id'],
                        'product_name'   => $data['product_name'],
                        'sale_price'     => $data['sale_price'],
                        'quantity'       => $data['quantity'],
                        'total'          => $data['total'],
                    ]);

                    // Redirigir al formulario de edición de este Sale (donde ItemsRelationManager mostrará el item)
                    return Redirect::to(
                        SaleResource::getUrl('edit', ['record' => $sale->id])
                    );
                })
                ->requiresConfirmation()
                ->visible(fn(): bool => Gate::allows('create', Sale::class)),
        ];
    }
}
