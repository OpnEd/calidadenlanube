<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Eliminar FK (si existe) apuntando a batches
        $fkRows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stocks'
              AND COLUMN_NAME = 'batch_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fkRows as $row) {
            // la propiedad puede ser CONSTRAINT_NAME o constraint_name según PDO driver
            $constraintName = $row->CONSTRAINT_NAME ?? $row->constraint_name ?? null;
            if ($constraintName) {
                // DROP FOREIGN KEY requiere el nombre del constraint
                DB::statement(sprintf('ALTER TABLE `stocks` DROP FOREIGN KEY `%s`', $constraintName));
            }
        }

        // 2) Eliminar índices (si existen) que incluyan batch_id (excepto PRIMARY)
        $indexRows = DB::select("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stocks'
              AND COLUMN_NAME = 'batch_id'
        ");

        foreach ($indexRows as $idx) {
            $indexName = $idx->INDEX_NAME ?? $idx->index_name ?? null;
            if ($indexName && strtoupper($indexName) !== 'PRIMARY') {
                DB::statement(sprintf('ALTER TABLE `stocks` DROP INDEX `%s`', $indexName));
            }
        }

        // 3) Renombrar la columna batch_id -> central_batch_id (adecuar tipo si tu DB difiere)
        // Asumimos BIGINT UNSIGNED; si tu columna original NO es unsigned, ajusta el tipo.
        DB::statement('ALTER TABLE `stocks` CHANGE `batch_id` `central_batch_id` BIGINT UNSIGNED');

        // 4) Crear nueva FK hacia central_batches(id)
        // Solo crearla si la columna existe
        $hasColumn = Schema::hasColumn('stocks', 'central_batch_id');
        if ($hasColumn) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->foreign('central_batch_id')
                    ->references('id')
                    ->on('central_batches')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Revertir: eliminar FK hacia central_batches, renombrar columna y restaurar FK hacia batches

        // 1) Eliminar FK en central_batch_id si existe
        $fkRows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stocks'
              AND COLUMN_NAME = 'central_batch_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($fkRows as $row) {
            $constraintName = $row->CONSTRAINT_NAME ?? $row->constraint_name ?? null;
            if ($constraintName) {
                DB::statement(sprintf('ALTER TABLE `stocks` DROP FOREIGN KEY `%s`', $constraintName));
            }
        }

        // 2) Eliminar índices sobre central_batch_id si existen (salta PRIMARY)
        $indexRows = DB::select("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stocks'
              AND COLUMN_NAME = 'central_batch_id'
        ");
        foreach ($indexRows as $idx) {
            $indexName = $idx->INDEX_NAME ?? $idx->index_name ?? null;
            if ($indexName && strtoupper($indexName) !== 'PRIMARY') {
                DB::statement(sprintf('ALTER TABLE `stocks` DROP INDEX `%s`', $indexName));
            }
        }

        // 3) Renombrar central_batch_id -> batch_id
        DB::statement('ALTER TABLE `stocks` CHANGE `central_batch_id` `batch_id` BIGINT UNSIGNED');

        // 4) Restaurar FK hacia batches (si la columna existe)
        if (Schema::hasColumn('stocks', 'batch_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->foreign('batch_id')
                    ->references('id')
                    ->on('batches')
                    ->cascadeOnDelete();
            });
        }
    }
};
