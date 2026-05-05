<?php

namespace App\Observers;

use App\Models\AnesthesiaSheet;
use App\Models\KardexEntry;
use App\Models\ModelVersion;
use App\Services\InventoryUnitConverter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnesthesiaSheetObserver
{
    public function updating(AnesthesiaSheet $sheet)
    {
        $dirty = $sheet->getDirty();           // campos modificados
        $original = $sheet->getOriginal();     // valores antiguos

        $changes = [];
        foreach ($dirty as $field => $new) {
            // opcional: omitir campos irrelevantes como timestamps
            if (in_array($field, ['updated_at'])) {
                continue;
            }
            $changes[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $new,
            ];
        }

        if (!empty($changes)) {
            ModelVersion::create([
                'team_id'           => $sheet->team_id,
                'versionable_type'  => $sheet->getMorphClass(),
                'versionable_id'    => $sheet->id,
                'user_id'           => Auth::id(),
                'changes'           => $changes,
                'snapshot'          => $sheet->toArray(),
                'comment'           => null,
            ]);
        }
    }
    /**
     * Handle the AnesthesiaSheet "created" event.
     */
    public function created(AnesthesiaSheet $anesthesiaSheet): void
    {
        //
    }

    /**
     * Handle the AnesthesiaSheet "updated" event.
     */
    public function updated(AnesthesiaSheet $sheet): void
    {
        $originalStatus = $sheet->getOriginal('status');
        $currentStatus = $sheet->status;
        $inventoryUnitConverter = app(InventoryUnitConverter::class);

        // Si el estado no cambió, no hacemos nada.
        if (!$sheet->wasChanged('status')) {
            return;
        }

        // CASO 1: La hoja se cierra (pasa de 'opened' a 'closed'). Se descuenta el inventario.
        if ($originalStatus === 'opened' && $currentStatus === 'closed' && !$sheet->consumed) {
            DB::transaction(function () use ($sheet, $inventoryUnitConverter) {
                foreach ($sheet->anesthesiaItems as $item) {
                    $inventory = $item->inventory()->with('product')->lockForUpdate()->first();

                    if ($inventory) {
                        $convertedQuantity = $inventoryUnitConverter->doseToStorageUnits(
                            (float) $item->dose_measure,
                            (string) $item->dose_measure_unit,
                            $inventory->product
                        );

                        if ($convertedQuantity <= 0) {
                            continue;
                        }

                        $stockBefore = (int) $inventory->quantity;
                        $inventory->decrement('quantity', $convertedQuantity);
                        KardexEntry::create([
                            'team_id' => $sheet->team_id,
                            'inventory_id' => $item->inventory_id,
                            'anesthesia_sheet_id' => $sheet->id,
                            'anesthesia_sheet_item_id' => $item->id,
                            'recipebook_id' => $sheet->recipebook_id,
                            'movement_date' => now(),
                            'movement_type' => 'out',
                            'quantity' => $convertedQuantity,
                            'unit' => $inventoryUnitConverter->movementUnit($inventory->product, (string) $item->dose_measure_unit),
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockBefore - $convertedQuantity,
                            'notes' => $sheet->anesthesia_notes,
                        ]);
                    }
                }
                $sheet->consumed = true;
                $sheet->saveQuietly();
            });
        }

        // CASO 2: La hoja se cancela DESPUÉS de haber sido cerrada (pasa de 'closed' a 'canceled'). Se repone el inventario.
        if ($originalStatus === 'closed' && $currentStatus === 'canceled' && $sheet->getOriginal('consumed')) {
            DB::transaction(function () use ($sheet, $inventoryUnitConverter) {
                foreach ($sheet->anesthesiaItems as $item) {
                    $inventory = $item->inventory()->with('product')->lockForUpdate()->first();

                    if ($inventory) {
                        $convertedQuantity = $inventoryUnitConverter->doseToStorageUnits(
                            (float) $item->dose_measure,
                            (string) $item->dose_measure_unit,
                            $inventory->product
                        );

                        if ($convertedQuantity <= 0) {
                            continue;
                        }

                        $stockBefore = (int) $inventory->quantity;
                        $inventory->increment('quantity', $convertedQuantity);
                        KardexEntry::create([
                            'team_id' => $sheet->team_id,
                            'inventory_id' => $item->inventory_id,
                            'anesthesia_sheet_id' => $sheet->id,
                            'anesthesia_sheet_item_id' => $item->id,
                            'recipebook_id' => $sheet->recipebook_id,
                            'movement_date' => now(),
                            'movement_type' => 'in',
                            'quantity' => $convertedQuantity,
                            'unit' => $inventoryUnitConverter->movementUnit($inventory->product, (string) $item->dose_measure_unit),
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockBefore + $convertedQuantity,
                            'notes' => "Reposición por cancelación de hoja de anestesia: {$sheet->recipebook->consecutive}",
                        ]);
                    }
                }
                if ($sheet->recipebook) {
                    $sheet->recipebook->update(['status' => 'available']);
                }
                $sheet->consumed = false;
                $sheet->saveQuietly();
            });
        }

        // CASO 3: La hoja se cancela aún estando abierta (pasa de 'opened' a 'canceled'). Se repone el inventario.
        if ($originalStatus === 'opened' && $currentStatus === 'canceled' && !$sheet->getOriginal('consumed')) {
            DB::transaction(function () use ($sheet, $inventoryUnitConverter) {
                foreach ($sheet->anesthesiaItems as $item) {
                    $inventory = $item->inventory()->with('product')->lockForUpdate()->first();

                    if ($inventory) {
                        $convertedQuantity = $inventoryUnitConverter->doseToStorageUnits(
                            (float) $item->dose_measure,
                            (string) $item->dose_measure_unit,
                            $inventory->product
                        );

                        if ($convertedQuantity <= 0) {
                            continue;
                        }

                        $inventory->increment('quantity', $convertedQuantity);
                    }
                }
                if ($sheet->recipebook) {
                    $sheet->recipebook->update(['status' => 'available']);
                }
                $sheet->consumed = false;
                $sheet->saveQuietly();
            });
        }
    }

    /**
     * Handle the AnesthesiaSheet "deleted" event.
     */
    public function deleted(AnesthesiaSheet $anesthesiaSheet): void
    {
        //
    }

    /**
     * Handle the AnesthesiaSheet "restored" event.
     */
    public function restored(AnesthesiaSheet $anesthesiaSheet): void
    {
        //
    }

    /**
     * Handle the AnesthesiaSheet "force deleted" event.
     */
    public function forceDeleted(AnesthesiaSheet $anesthesiaSheet): void
    {
        //
    }
}
