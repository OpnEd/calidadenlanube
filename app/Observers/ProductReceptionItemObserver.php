<?php

namespace App\Observers;

use App\Models\ProductReception;
use App\Models\ProductReceptionItem;
use Illuminate\Validation\ValidationException;

class ProductReceptionItemObserver
{
    public function creating(ProductReceptionItem $item): void
    {
        $this->ensureReceptionIsEditable($item->product_reception_id);
    }

    public function updating(ProductReceptionItem $item): void
    {
        $originalReceptionId = (int) $item->getOriginal('product_reception_id');

        if ($originalReceptionId > 0) {
            $this->ensureReceptionIsEditable($originalReceptionId);
        }

        $this->ensureReceptionIsEditable($item->product_reception_id);
    }

    public function deleting(ProductReceptionItem $item): void
    {
        $this->ensureReceptionIsEditable($item->product_reception_id);
    }

    private function ensureReceptionIsEditable(?int $receptionId): void
    {
        if (! $receptionId) {
            return;
        }

        $reception = ProductReception::withTrashed()->find($receptionId);

        if ($reception?->isDone()) {
            throw ValidationException::withMessages([
                'product_reception_id' => 'No se pueden modificar ítems de una recepción confirmada.',
            ]);
        }
    }
}
