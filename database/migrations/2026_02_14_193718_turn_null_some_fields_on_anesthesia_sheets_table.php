<?php

use App\Models\Recipebook;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anesthesia_sheets', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('surgeon_id')->nullable()->change();
            $table->unsignedBigInteger('pet_id')->nullable()->change();
            //$table->dropColumn('recipe_number');
            $table->unsignedBigInteger('recipebook_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->unique()
                  ->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anesthesia_sheets', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->unsignedBigInteger('surgeon_id')->nullable(false)->change();
            $table->unsignedBigInteger('pet_id')->nullable(false)->change();
            $table->string('recipe_number', 50)->nullable();
            $table->dropUnique(['recipebook_id']);

            $table->unsignedBigInteger('recipebook_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->change();

        });
    }
};
