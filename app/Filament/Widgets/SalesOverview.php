<?php

namespace App\Filament\Widgets;

use App\Models\AnesthesiaSheet;
use App\Models\Inventory;
use App\Models\Recipebook;
use App\Models\TeamProductPrice;
use App\Services\InventoryUnitConverter;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Resources\AnesthesiaSheetResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->id;
        $lastOpenedSheet = AnesthesiaSheet::where('team_id', $tenantId)->where('status', 'opened')->latest('created_at')->first();
        $lastClosedSheet = AnesthesiaSheet::where('team_id', $tenantId)->where('status', 'closed')->latest('closed_at')->first();
        $inventoryUnitConverter = app(InventoryUnitConverter::class);

        $mces = Inventory::query()
            ->where('team_id', $tenantId)
            ->with('product')
            ->whereHas('product', fn($q) => $q->where('is_mce', true))
            ->get();

        $mceGrouped = $mces->groupBy(function (Inventory $inventory): string {
            if ($inventory->product_id) {
                return 'product:' . $inventory->product_id;
            }

            return 'name:' . mb_strtolower((string) $inventory->product_name);
        });

        $productIds = $mceGrouped
            ->map(fn ($group) => optional($group->first())->product_id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $teamThresholdByProduct = collect();
        $peripheralThresholdByProduct = collect();

        if ($tenantId && ! empty($productIds)) {
            $teamThresholdByProduct = TeamProductPrice::query()
                ->where('team_id', $tenantId)
                ->whereIn('product_id', $productIds)
                ->pluck('min', 'product_id');

            if (Schema::hasTable('peripheral_product_price')) {
                $peripheralThresholdByProduct = DB::table('peripheral_product_price')
                    ->where('team_id', $tenantId)
                    ->whereIn('product_id', $productIds)
                    ->pluck('min_stock', 'product_id');
            }
        }

        $mceStats = $mceGrouped
            ->map(function ($groupedInventories) use (
                $inventoryUnitConverter,
                $teamThresholdByProduct,
                $peripheralThresholdByProduct
            ) {
                /** @var Inventory $firstInventory */
                $firstInventory = $groupedInventories->first();
                $product = $firstInventory->product;
                $productName = (string) ($product?->name ?? $firstInventory->product_name ?? 'Medicamento');

                $commercialUnitsRaw = (float) $groupedInventories->sum(fn (Inventory $inventory): float => (float) $inventory->quantity);
                $commercialUnits = $commercialUnitsRaw;

                if (fmod($commercialUnitsRaw, 1.0) !== 0.0) {
                    $commercialUnits = (float) ceil($commercialUnitsRaw);
                }

                $minimumUnits = $inventoryUnitConverter->commercialToStorageUnits($commercialUnitsRaw, $product);

                $commercialUnitLabel = 'Uds.';

                $productId = $product?->id;
                $thresholdCommercial = null;

                if ($productId) {
                    $thresholdCommercial = $teamThresholdByProduct->get($productId);
                    if ($thresholdCommercial === null) {
                        $thresholdCommercial = $peripheralThresholdByProduct->get($productId);
                    }
                }

                $thresholdCommercial = $thresholdCommercial !== null ? (float) $thresholdCommercial : null;
                $thresholdMinimumUnits = $thresholdCommercial !== null
                    ? $inventoryUnitConverter->commercialToStorageUnits($thresholdCommercial, $product)
                    : null;

                $isLowStock = $thresholdMinimumUnits !== null
                    && $thresholdMinimumUnits > 0
                    && $commercialUnitsRaw <= $thresholdCommercial;

                $description = number_format($minimumUnits, 0, ',', '.') . ' u-min';

                $conversionFactor = (float) ($product?->conversion_factor ?? 0);
                $minFraction = (float) ($product?->min_fraction ?? 0);

                if ($conversionFactor > 0 && $minFraction > 0) {
                    $totalMl = $commercialUnitsRaw * $conversionFactor * $minFraction;
                    $description .= ' | ' . number_format($totalMl, 0, ',', '.') . ' mL';
                }

                if ($thresholdCommercial !== null && $thresholdCommercial > 0) {
                    $description .= ' | Min: '
                        . number_format($thresholdCommercial, 0, ',', '.')
                        . ' ' . $commercialUnitLabel;
                }

                return Stat::make(
                    $productName,
                    number_format($commercialUnits, 0, ',', '.') . ' ' . $commercialUnitLabel
                )
                ->description($description)
                ->descriptionIcon('phosphor-syringe')
                ->color($isLowStock ? 'warning' : 'success');
            },)
            ->values()
            ->all();

        return [
            Stat::make(
                "Última Hoja de Anesthesia",
                $lastClosedSheet?->closed_at?->format('d/m/Y H:i') ?? 'N/A'
            )
                ->url(
                    $lastClosedSheet
                        ? AnesthesiaSheetResource::getUrl('view', ['record' => $lastClosedSheet])
                        : null
                )
                ->description('Ir a la última hoja de anestesia cerrada')
                ->descriptionIcon('phosphor-file-text')
                ->color('success'),
            Stat::make("Hojas de Anestesia", AnesthesiaSheet::query()
                ->where('team_id', $tenantId)
                ->where("closed_at", ">=", now()->startOfMonth())
                ->count())
                ->description('Desde inicio de mes')
                ->descriptionIcon('phosphor-files')
                ->color('success'),
            Stat::make("Hojas de Anestesia Canceladas", AnesthesiaSheet::query()
                ->where('team_id', $tenantId)
                ->where("closed_at", ">=", now()->startOfMonth())
                ->where("status", "=", "canceled")
                ->count())
                ->description('Desde inicio de mes')
                ->descriptionIcon('phosphor-file-dashed')
                ->color('success'),
            Stat::make("Recetas disponibles", Recipebook::query()
                ->where('team_id', $tenantId)
                ->where(
                "status",
                "=",
                "available"
            )->count())
                ->description('Recetas disponibles en el sistema')
                ->descriptionIcon('phosphor-asclepius')
                ->color('success'),
            ...$mceStats,
        ];
    }
}
