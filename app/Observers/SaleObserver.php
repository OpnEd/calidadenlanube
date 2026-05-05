<?php

namespace App\Observers;

use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     * Después de crear la venta y sus SaleItems, descuenta cantidades de inventario según el lote.
     *
     * @param  \App\Models\Sale  $sale
     * @return void
     */
   /*  public function created(Sale $sale): void
    {
        // Descuenta cantidades de inventario por lote para cada SaleItem
        foreach ($sale->items as $item) {
            $inventory = $item->inventory;
            if ($inventory && $item->quantity > 0) {
                // Si el modelo Inventory tiene relación con lotes, descuenta por lote
                if (method_exists($inventory, 'batches') && $inventory->batches()->exists()) {
                    $remaining = $item->quantity;
                    $batches = $inventory->batches()
                        ->where('quantity', '>', 0)
                        ->orderBy('expiration_date') // FIFO: primero los que vencen antes
                        ->get();

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) break;
                        $deduct = min($batch->quantity, $remaining);
                        $batch->decrement('quantity', $deduct);
                        $remaining -= $deduct;
                    }
                } else {
                    // Si no hay lotes, descuenta del inventario general
                    $inventory->decrement('quantity', $item->quantity);
                }
            }
        }
        //Log::info("Stock decremented by batch for Sale {$sale->id} creation.");
    } */
    /**
     * Handle the Sale "updated" event.
     * Detecta cambios en el campo `status` para ajustar el inventario:
     * - De 'pending' a 'completed': decrementa stock.
     * - De 'completed' a 'cancelled': repone stock.
     *
     * @param  \App\Models\Sale  $sale
     * @return void
     */
    public function updated(Sale $sale): void
    {
        $originalStatus = $sale->getOriginal('status');
        $currentStatus = $sale->status;

        // Asegúrate de que los logs se escriban correctamente
        //Log::channel('single')->debug("SaleObserver@updated: Sale ID {$sale->id}, original status: {$originalStatus}, current status: {$currentStatus}");

        // Si la venta pasa de 'completed' a 'cancelled', reponer stock
        if ($originalStatus === 'completed' && $currentStatus === 'canceled') {
            foreach ($sale->items as $item) {
                //Log::channel('single')->debug("Processing SaleItem ID {$item->id}, quantity: {$item->quantity}");
                $inventory = $item->inventory;
                if ($inventory) {
                    //Log::channel('single')->debug("Found inventory ID {$inventory->id} for SaleItem ID {$item->id}");
                    // Si el inventario tiene lote, reponer en ese lote
                    if (method_exists($inventory, 'batch') && $inventory->batch()->exists()) {
                        $batch = $inventory->batch;
                        if ($batch) {
                            //Log::channel('single')->debug("Replenishing inventory ID {$inventory->id} for batch ID {$batch->id} with quantity {$item->quantity}");
                            // Incrementa la cantidad en Inventory donde batch_id = $batch->id
                            $inventory->where('batch_id', $batch->id)->increment('quantity', $item->quantity);
                        } else {
                            //Log::channel('single')->warning("No batch found for inventory ID {$inventory->id}");
                        }
                    } else {
                        // Si no hay lote, reponer al inventario general
                        //Log::channel('single')->debug("Replenishing general inventory ID {$inventory->id} with quantity {$item->quantity}");
                        $inventory->increment('quantity', $item->quantity);
                    }
                } else {
                    //Log::channel('single')->warning("No inventory found for SaleItem ID {$item->id}");
                }
            }
            //Log::channel('single')->info("Stock replenished by batch for Sale {$sale->id} cancellation.");
        }
        // Ya no es necesario decrementar stock aquí, porque la venta ya se crea como 'completed'
    }
}

// Registro del observer en AppServiceProvider.php (método boot):
// use App\Models\Sale;
// use App\Observers\SaleObserver;
// 
// public function boot()
// {
//     Sale::observe(SaleObserver::class);
// }
