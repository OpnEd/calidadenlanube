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
        if (! Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            // Límite de intentos permitidos (null = ilimitados)
            if (! Schema::hasColumn('assessments', 'max_attempts')) {
                $table->unsignedInteger('max_attempts')
                    ->nullable()
                    ->after('passing_score')
                    ->comment('Número máximo de intentos permitidos. NULL = ilimitados');
            }

            // Duración máxima en minutos (null = sin límite de tiempo)
            if (! Schema::hasColumn('assessments', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')
                    ->nullable()
                    ->after('max_attempts')
                    ->comment('Duración máxima permitida en minutos. NULL = sin límite');
            }

            // Si mostrar feedback/respuestas correctas después de evaluar
            if (! Schema::hasColumn('assessments', 'show_feedback')) {
                $table->boolean('show_feedback')
                    ->default(true)
                    ->after('duration_minutes')
                    ->comment('Mostrar retroalimentación y respuestas correctas al estudiante');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (Schema::hasColumn('assessments', 'max_attempts')) {
                    $table->dropColumn('max_attempts');
                }
                if (Schema::hasColumn('assessments', 'duration_minutes')) {
                    $table->dropColumn('duration_minutes');
                }
                if (Schema::hasColumn('assessments', 'show_feedback')) {
                    $table->dropColumn('show_feedback');
                }
            });
        }
    }
};
