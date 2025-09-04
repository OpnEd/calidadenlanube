<?php

use App\Models\Quality\Training\Assessment;
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
            // Primero eliminamos la clave foránea y la columna de Training
            $table->dropForeign(['training_id']);
            $table->dropColumn('training_id');

            // Creamos la nueva relación con Assessment
            $table->foreignIdFor(Assessment::class)
                ->after('team_id')
                ->constrained()
                ->onDelete('cascade');
            $table->enum('type', ['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'free_text'])
                ->default('multiple_choice_single')
                ->comment('E.g., multiple_choice_single, multiple_choice_multiple, true_false, free_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Revertir: eliminar assessment_id y restaurar training_id
            $table->dropForeign(['assessment_id']);
            $table->dropColumn('assessment_id');

            $table->foreignId('training_id')
                ->after('team_id')
                ->constrained()
                ->onDelete('cascade');
        });
    }
};
