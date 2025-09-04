<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    // 1. Convertir datos existentes a JSON válido antes de cambiar el tipo
        DB::table('lessons')->get()->each(function ($lesson) {
            $content = $lesson->content;

            // Si está vacío o null, poner un objeto vacío
            if (is_null($content) || trim($content) === '') {
                $content = '{}';
            }

            // Si no es JSON válido, envolverlo como string JSON
            json_decode($content);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $content = json_encode(['text' => $content], JSON_UNESCAPED_UNICODE);
            }

            DB::table('lessons')
                ->where('id', $lesson->id)
                ->update(['content' => $content]);
        });

        // 2. Cambiar el tipo de columna a JSON
        Schema::table('lessons', function (Blueprint $table) {
            $table->json('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }
};
