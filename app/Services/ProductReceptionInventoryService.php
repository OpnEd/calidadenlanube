<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ProductReception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReceptionInventoryService
{
    public function __construct(
        private readonly InventoryUnitConverter $inventoryUnitConverter,
    ) {
    }

    /**
     * Confirma una recepción y suma sus ítems al inventario por lote.
     * Retorna false si ya estaba confirmada.
     */
    public function confirm(ProductReception $reception): bool
    {
        return DB::transaction(function () use ($reception): bool {
            /** @var ProductReception $lockedReception */
            $lockedReception = ProductReception::query()
                ->with(['items.product'])
                ->lockForUpdate()
                ->findOrFail($reception->getKey());

            if ($lockedReception->isDone()) {
                return false;
            }

            if ($lockedReception->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'La recepción no tiene ítems para confirmar.',
                ]);
            }

            if ($lockedReception->items->contains(fn ($item) => blank($item->batch_id))) {
                throw ValidationException::withMessages([
                    'batch_id' => 'Todos los productos deben tener un lote asignado.',
                ]);
            }

            foreach ($lockedReception->items as $item) {
                if ((int) $item->quantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Todos los ítems deben tener cantidad mayor a cero.',
                    ]);
                }

                $inventory = Inventory::query()
                    ->lockForUpdate()
                    ->firstOrNew([
                        'team_id' => $lockedReception->team_id,
                        'product_id' => $item->product_id,
                        'batch_id' => $item->batch_id,
                    ]);

                if (! $inventory->exists) {
                    $inventory->quantity = 0;
                }

                $receivedStorageUnits = $this->inventoryUnitConverter
                    ->commercialToStorageUnits((float) $item->quantity, $item->product);

                $inventory->quantity = (int) $inventory->quantity + $receivedStorageUnits;
                $inventory->purchase_price = $item->purchase_price;
                $inventory->product_name = $item->product?->name ?? ($inventory->product_name ?: 'Producto');
                $inventory->save();
            }

            $lockedReception->update([
                'status' => ProductReception::STATUS_DONE,
                'reception_date' => $lockedReception->reception_date ?? now(),
            ]);

            return true;
        });
    }
}
