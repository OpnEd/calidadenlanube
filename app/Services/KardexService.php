<?php

use App\Models\AnesthesiaSheetItem;
use App\Models\Inventory;
use App\Models\KardexEntry;
use App\Services\InventoryUnitConverter;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class KardexService
{
    public function handleAnesthesiaItemUpdate(
        AnesthesiaSheetItem $item,
        array $original
    ): void {
        $inventoryUnitConverter = app(InventoryUnitConverter::class);

        DB::transaction(function () use ($item, $original, $inventoryUnitConverter): void {
            $oldInventoryId = (int) ($original['inventory_id'] ?? 0);
            $newInventoryId = (int) ($item->inventory_id ?? 0);

            $oldDose = (float) ($original['dose_measure'] ?? 0);
            $newDose = (float) ($item->dose_measure ?? 0);

            $oldUnit = (string) ($original['dose_measure_unit'] ?? '');
            $newUnit = (string) ($item->dose_measure_unit ?? '');

            $oldInventory = $oldInventoryId ? Inventory::with('product')->find($oldInventoryId) : null;
            $newInventory = $newInventoryId ? Inventory::with('product')->find($newInventoryId) : null;

            $oldDoseStorage = $inventoryUnitConverter->doseToStorageUnits(
                $oldDose,
                $oldUnit,
                $oldInventory?->product
            );

            $newDoseStorage = $inventoryUnitConverter->doseToStorageUnits(
                $newDose,
                $newUnit,
                $newInventory?->product
            );

            if ($oldInventoryId !== $newInventoryId) {
                $this->registerMovement(
                    item: $item,
                    inventoryId: $oldInventoryId,
                    quantity: $oldDoseStorage,
                    type: 'in',
                    notes: "Reintegro por cambio de medicamento (Anestesia #{$item->id})"
                );

                $this->registerMovement(
                    item: $item,
                    inventoryId: $newInventoryId,
                    quantity: $newDoseStorage,
                    type: 'out',
                    notes: "Consumo por cambio de medicamento (Anestesia #{$item->id})"
                );

                return;
            }

            $difference = $newDoseStorage - $oldDoseStorage;

            if ($difference !== 0) {
                $this->registerMovement(
                    item: $item,
                    inventoryId: $newInventoryId,
                    quantity: abs($difference),
                    type: $difference > 0 ? 'out' : 'in',
                    notes: "Ajuste por modificacion de dosis (Anestesia #{$item->id})"
                );
            }
        });
    }

    protected function registerMovement(
        AnesthesiaSheetItem $item,
        int $inventoryId,
        float|int $quantity,
        string $type,
        string $notes
    ): void {
        if ($inventoryId <= 0 || $quantity <= 0) {
            return;
        }

        $inventory = Inventory::with('product')
            ->lockForUpdate()
            ->findOrFail($inventoryId);

        $inventoryUnitConverter = app(InventoryUnitConverter::class);
        $quantity = (int) round((float) $quantity, 0, PHP_ROUND_HALF_UP);
        $previous = (int) $inventory->quantity;

        $new = $type === 'out'
            ? $previous - $quantity
            : $previous + $quantity;

        $inventory->update([
            'quantity' => $new,
        ]);

        KardexEntry::create([
            'team_id' => Filament::getTenant()->id,
            'inventory_id' => $inventoryId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $previous,
            'stock_after' => $new,
            'notes' => $notes,
            'user_id' => auth()->id(),
            'anesthesia_sheet_id' => $item->anesthesia_sheet_id,
            'anesthesia_sheet_item_id' => $item->id,
            'recipebook_id' => $item->recipebook_id,
            'movement_date' => now(),
            'movement_type' => $type,
            'unit' => $inventoryUnitConverter->movementUnit($inventory->product, (string) $item->dose_measure_unit),
        ]);
    }
}

