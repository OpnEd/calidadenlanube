<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // 1. Agregamos las nuevas columnas primero
            $table->json('ilustrations')->nullable()->after('order');//
            $table->json('objectives')->nullable()->after('ilustrations');//
            $table->text('introduction')->nullable()->after('objectives');//
            $table->json('conclusions')->nullable()->after('content');
            $table->json('references')->nullable()->after('conclusions');
        });

        // 2. Migramos el contenido de 'objective' (string) al nuevo campo 'objectives' (json array)
        DB::table('lessons')->whereNotNull('objective')->chunkById(100, function ($lessons) {
            foreach ($lessons as $lesson) {
                DB::table('lessons')
                    ->where('id', $lesson->id)
                    ->update(['objectives' => json_encode([$lesson->objective])]);
            }
        });

        // 3. Ahora que los datos están seguros, eliminamos la columna antigua
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('objective');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('objective')->nullable()->after('title');
        });

        // Revertimos datos: tomamos el primer elemento del array JSON para restaurar el string
        DB::table('lessons')->whereNotNull('objectives')->chunkById(100, function ($lessons) {
            foreach ($lessons as $lesson) {
                $objectives = json_decode($lesson->objectives, true);
                if (is_array($objectives) && !empty($objectives)) {
                    DB::table('lessons')
                        ->where('id', $lesson->id)
                        ->update(['objective' => $objectives[0]]);
                }
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'ilustrations',
                'objectives',
                'introduction',
                'content',
                'conclusions',
                'references'
            ]);
        });
    }
};
