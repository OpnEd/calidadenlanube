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
        Schema::table('kardex_entries', function (Blueprint $table) {
            // Relación con versión que lo originó
            $table->foreignId('model_version_id')
                ->nullable()
                ->constrained('model_versions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kardex_entries', function (Blueprint $table) {
            $table->dropForeign(['model_version_id']);
            $table->dropColumn('model_version_id');
        });
    }
};
