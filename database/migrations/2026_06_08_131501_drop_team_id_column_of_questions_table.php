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
        Schema::table('questions', function (Blueprint $table) {
            // Primero eliminamos la foreign key
            $table->dropForeign(['team_id']);
            // Luego eliminamos la columna
            $table->dropColumn('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Restaurar la columna y la relación si haces rollback
            $table->foreignIdFor(\App\Models\Team::class)
                  ->constrained()
                  ->onDelete('cascade');
        });
    }
};
