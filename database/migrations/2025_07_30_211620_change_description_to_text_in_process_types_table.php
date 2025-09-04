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
        Schema::table('process_types', function (Blueprint $table) {
            // Cambiamos de string a text
            $table->text('description')
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_types', function (Blueprint $table) {
            // Volvemos a string (por defecto varchar(255))
            $table->string('description', 255)
                  ->nullable()
                  ->change();
        });
    }
};
