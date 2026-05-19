<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DispatchService
{
    /**
     * Crea un Dispatch a partir de una Purchase.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \App\Models\Dispatch
     * @throws \Exception Si la compra no está en un estado válido.
     */
    public function createFromPurchase(Purchase $purchase): Dispatch
    {
        Log::info("Iniciando creación de Dispatch para Purchase ID: {$purchase->id} (Estado: " . ($purchase->status->value ?? $purchase->status) . ")");
        // Validar que la compra esté confirmada (opcional, de acuerdo a tu lógica)
        if ($purchase->status->value !== 'confirmed') {
            throw new \Exception('La compra no está confirmada.');
        }

        // 1. Crear el Dispatch
        $dispatch = Dispatch::create([
            'purchase_id'   => $purchase->id,
            'team_id'       => $purchase->team_id,
            'user_id'       => Auth::id(),
            'dispatched_at' => now(),
            'total'         => $purchase->total,
            'data'          => $purchase->data,
        ]);

        // 2. Clonar cada PurchaseItem en DispatchItem (sin asignar lote aún)
        $itemsData = $purchase->items->map(function ($item) {
            return [
                'purchase_item_id' => $item->id,
                'batch_id'         => null,
                'quantity'         => $item->quantity,
                'price'            => $item->price,
                'total'            => $item->total,
            ];
        })->toArray();

        $dispatch->items()->createMany($itemsData);

        // Cambiar el estado de la compra a 'in progress'
        $purchase->status = 'in progress';
        $purchase->save();

        // Retornar el Dispatch creado para posteriores operaciones.
        return $dispatch;
    }
}