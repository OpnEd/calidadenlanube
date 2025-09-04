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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->enum('type', ['quiz', 'exam', 'assignment']);
            $table->float('max_score')->default(100);
            $table->float('passing_score')->default(60);
            $table->integer('duration')->default(60); // Duration in minutes
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
