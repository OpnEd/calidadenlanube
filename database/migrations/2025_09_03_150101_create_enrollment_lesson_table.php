<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enrollment_lesson', function (Blueprint $table) {
            $table->id(); // bigint unsigned + auto_increment
            $table->timestamps(); // created_at y updated_at

            $table->foreignId('enrollment_id')
                  ->nullable()
                  ->constrained('enrollments')
                  ->onDelete('cascade');

            $table->foreignId('lesson_id')
                  ->nullable()
                  ->constrained('lessons')
                  ->onDelete('cascade');

            $table->string('status', 15)
                  ->default('not_started')
                  ->collation('utf8mb4_unicode_ci');

            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->date('last_accessed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollment_lesson');
    }
};
