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
        Schema::create('model_versions', function (Blueprint $table) {

            $table->id();

            // Multitenancy
            $table->foreignIdFor(\App\Models\Team::class)
                ->constrained()
                ->cascadeOnDelete();

            // Polimorfismo
            $table->string('versionable_type');
            $table->unsignedBigInteger('versionable_id');

            // Quién hizo el cambio
            $table->foreignId('user_id')
                ->constrained('users');

            // Qué cambió
            $table->json('changes');

            // Snapshot opcional del estado completo
            $table->json('snapshot')->nullable();

            // Comentario
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index(['versionable_type', 'versionable_id']);
            $table->index(['team_id', 'versionable_type', 'versionable_id']);
            $table->index(['created_at']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_versions');
    }
};
