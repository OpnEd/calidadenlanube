<?php

namespace Database\Seeders;

use App\Models\CentralBatch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->command?->warn('No hay productos. Ejecutando ProductSeeder...');
            $this->call(ProductSeeder::class);
        }

        if (CentralBatch::count() === 0) {
            $this->command?->warn('No hay central batches. Ejecutando CentralBatchSeeder...');
            $this->call(CentralBatchSeeder::class);
        }

        $productIds = Product::query()->pluck('id')->all();
        $centralBatchIds = CentralBatch::query()->pluck('id')->all();

        if (empty($productIds) || empty($centralBatchIds)) {
            $this->command?->error('StockSeeder: faltan productos o central batches para sembrar stock.');

            return;
        }

        foreach ($productIds as $productId) {
            Stock::updateOrCreate(
                [
                    'product_id' => $productId,
                    'central_batch_id' => $centralBatchIds[array_rand($centralBatchIds)],
                ],
                [
                    'quantity' => random_int(5, 120),
                    'purchase_price' => random_int(300, 25000) / 100,
                ]
            );
        }

        $targetRecords = max(120, count($productIds));
        $currentCount = Stock::count();
        $attempts = 0;
        $maxAttempts = 2000;

        while ($currentCount < $targetRecords && $attempts < $maxAttempts) {
            $attempts++;

            $productId = $productIds[array_rand($productIds)];
            $centralBatchId = $centralBatchIds[array_rand($centralBatchIds)];

            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'central_batch_id' => $centralBatchId,
                ],
                [
                    'quantity' => random_int(1, 90),
                    'purchase_price' => random_int(200, 30000) / 100,
                ]
            );

            if ($stock->wasRecentlyCreated) {
                $currentCount++;
            }
        }

        Stock::query()
            ->inRandomOrder()
            ->limit(min(40, Stock::count()))
            ->get()
            ->each(function (Stock $stock): void {
                $stock->update([
                    'quantity' => max(0, (int) $stock->quantity + random_int(-3, 15)),
                    'purchase_price' => max(0.01, (float) $stock->purchase_price + (random_int(-50, 120) / 100)),
                ]);
            });

        $this->command?->info('StockSeeder: stock sembrado usando product_id + central_batch_id.');
    }
}
