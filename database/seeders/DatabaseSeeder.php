<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SanitaryRegistry;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

         $this->call([
            TeamSeeder::class,
            TeamUserSeeder::class,
            ManufacturerSeeder::class,
            ProductCategorySeeder::class,
            PharmaceuticalFormSeeder::class,
            ProductSeeder::class,
            SanitaryRegistrySeeder::class,
            BatchSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            DocumentCategorySeeder::class,
            RoleSeeder::class,
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
            CentralBatchSeeder::class,
            StockSeeder::class,
            CentralProductPriceSeeder::class,
            InventorySeeder::class,
            PeripheralProductPriceSeeder::class,
            EnvironmentalRecordSeeder::class,
            PetSeeder::class,
        ]);

        /* $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */

        /* $user = User::find(1);
        $role = Role::find(20);

        $user->assignRole($role); */
    }
}
