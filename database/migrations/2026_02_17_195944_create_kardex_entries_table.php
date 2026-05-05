<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kardex_entries', function (Blueprint $table) {
            $table->id();

            // Multitenant
            $table->foreignId('team_id')
                ->constrained()
                ->cascadeOnDelete();

            // Relación con el ítem de inventario
            $table->foreignId('inventory_id')
                ->constrained('inventories');

            // Contexto clínico
            $table->foreignId('anesthesia_sheet_id')
                ->nullable()
                ->constrained('anesthesia_sheets')
                ->nullOnDelete();

            $table->foreignId('anesthesia_sheet_item_id')
                ->nullable()
                ->constrained('anesthesia_sheet_items')
                ->nullOnDelete();

            // Recetario oficial (Recipebook)
            $table->foreignId('recipebook_id')
                ->nullable()
                ->constrained('recipebooks')
                ->nullOnDelete();

            // Datos del movimiento
            $table->dateTime('movement_date')->index();

            // Tipo de movimiento: por ahora casi siempre 'out', pero queda abierto a 'in', 'adjust'
            $table->enum('movement_type', ['in', 'out', 'adjust'])
                ->default('out')
                ->index();

            $table->decimal('quantity', 12, 2);
            $table->string('unit', 20)->nullable();

            // Saldos antes/después del movimiento
            $table->decimal('stock_before', 12, 2)->nullable();
            $table->decimal('stock_after', 12, 2)->nullable();

            // Observaciones (p.ej. “Consumo en anestesia”, correcciones, etc.)
            $table->text('notes')->nullable();

            // Para enlazar correcciones a un movimiento original (opcional pero muy útil)
            $table->foreignId('reference_kardex_entry_id')
                ->nullable()
                ->constrained('kardex_entries')
                ->nullOnDelete();

            // Motivo del ajuste
            $table->string('adjustment_reason')->nullable();

            $table->softDeletes();

            $table->timestamps();

            // Índices útiles
            $table->index(['team_id', 'inventory_id', 'movement_date']);
            $table->index(['team_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex_entries');
    }
};
