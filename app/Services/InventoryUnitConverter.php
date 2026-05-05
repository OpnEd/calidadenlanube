<?php

namespace App\Services;

use App\Models\Product;

class InventoryUnitConverter
{
    public function commercialToStorageUnits(float|int $quantity, ?Product $product): int
    {
        $factor = $this->resolveCommercialFactor($product);
        $storage = (float) $quantity * $factor;

        return max(0, (int) round($storage, 0, PHP_ROUND_HALF_UP));
    }

    public function storageToCommercialUnits(float|int $quantity, ?Product $product, int $precision = 4): float
    {
        $factor = $this->resolveCommercialFactor($product);

        if ($factor <= 0) {
            return 0.0;
        }

        return round((float) $quantity / $factor, $precision);
    }

    public function doseToStorageUnits(float|int $doseMeasure, ?string $doseMeasureUnit, ?Product $product): int
    {
        if (! $product?->fractionable) {
            return max(0, (int) round((float) $doseMeasure, 0, PHP_ROUND_HALF_UP));
        }

        $minFraction = (float) ($product->min_fraction ?? 0);

        if ($minFraction <= 0) {
            return max(0, (int) round((float) $doseMeasure, 0, PHP_ROUND_HALF_UP));
        }

        return max(0, (int) ceil((float) $doseMeasure / $minFraction));
    }

    public function movementUnit(?Product $product, ?string $defaultUnit = null): string
    {
        if ($product?->fractionable) {
            return 'u-min';
        }

        return (string) ($defaultUnit ?: ($product?->unit_measurement ?: 'u'));
    }

    private function resolveCommercialFactor(?Product $product): float
    {
        if (! $product?->fractionable) {
            return 1.0;
        }

        $factor = (float) ($product->conversion_factor ?? 0);

        return $factor > 0 ? $factor : 1.0;
    }
}

