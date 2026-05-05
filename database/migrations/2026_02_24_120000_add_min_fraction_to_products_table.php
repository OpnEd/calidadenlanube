<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_fraction', 12, 4)
                ->nullable()
                ->after('conversion_factor')
                ->comment('Minimum consumable fraction for fractionable products (e.g. 0.1 mL)');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_fraction');
        });
    }
};

