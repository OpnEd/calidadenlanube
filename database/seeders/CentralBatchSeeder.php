<?php

namespace Database\Seeders;

use App\Models\CentralBatch;
use App\Models\Manufacturer;
use App\Models\SanitaryRegistry;
use Illuminate\Database\Seeder;

class CentralBatchSeeder extends Seeder
{
    public function run(): void
    {
        if (Manufacturer::count() === 0) {
            $this->command?->warn('No hay fabricantes. Ejecutando ManufacturerSeeder...');
            $this->call(ManufacturerSeeder::class);
        }

        if (SanitaryRegistry::count() === 0) {
            $this->command?->warn('No hay registros sanitarios. Ejecutando SanitaryRegistrySeeder...');
            $this->call(SanitaryRegistrySeeder::class);
        }

        $manufacturerIds = Manufacturer::query()->pluck('id')->all();
        $sanitaryRegistryIds = SanitaryRegistry::query()->pluck('id')->all();

        if (empty($manufacturerIds) || empty($sanitaryRegistryIds)) {
            $this->command?->error('No se pudieron obtener manufacturer_id y sanitary_registry_id para central_batches.');

            return;
        }

        $toCreate = 80;
        $start = CentralBatch::count() + 1;

        for ($i = 0; $i < $toCreate; $i++) {
            $manufacturingDate = now()->subDays(random_int(60, 900));
            $expirationDate = (clone $manufacturingDate)->addDays(random_int(180, 1200));

            CentralBatch::create([
                'code' => sprintf('CB-%05d', $start + $i),
                'manufacturer_id' => $manufacturerIds[array_rand($manufacturerIds)],
                'sanitary_registry_id' => $sanitaryRegistryIds[array_rand($sanitaryRegistryIds)],
                'manufacturing_date' => $manufacturingDate->toDateString(),
                'expiration_date' => $expirationDate->toDateString(),
                'data' => [
                    'seeded' => true,
                    'source' => 'CentralBatchSeeder',
                ],
            ]);
        }

        $this->command?->info("CentralBatchSeeder: creados {$toCreate} lotes centrales.");
    }
}
