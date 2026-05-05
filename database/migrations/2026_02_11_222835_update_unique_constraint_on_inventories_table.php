<?php

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
        Schema::table('inventories', function (Blueprint $table) {
            // 1. Crear un índice explícito para product_id para sostener la llave foránea
            // antes de eliminar el índice único compuesto que la estaba sosteniendo.
            $table->index('product_id');

            // Eliminar el índice único existente (product_id, batch_id)
            // Laravel infiere el nombre: inventories_product_id_batch_id_unique
            $table->dropUnique(['product_id', 'batch_id']);

            // Crear el nuevo índice único compuesto incluyendo team_id
            $table->unique(['team_id', 'product_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropUnique(['team_id', 'product_id', 'batch_id']);
            
            // Restaurar el índice único original
            $table->unique(['product_id', 'batch_id']);
            
            // Eliminar el índice individual creado en up() ya que el unique original cubre la FK
            $table->dropIndex(['product_id']);
        });
    }
};
