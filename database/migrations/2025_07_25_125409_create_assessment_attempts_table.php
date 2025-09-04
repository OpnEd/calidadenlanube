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
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id'); // Foreign key to assessments
            $table->unsignedBigInteger('user_id'); // Foreign key to users
            $table->float('score')->default(0); // Score obtained in the attempt
            $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress'); // Status of the attempt
            $table->timestamp('started_at')->nullable(); // Start time of the attempt
            $table->timestamp('completed_at')->nullable(); // Completion time of the attempt
            $table->json('responses')->nullable(); // Responses given by the user (can be JSON)
            $table->boolean('passed')->default(false); // Indicates if the attempt was passed
            $table->text('feedback')->nullable(); // Optional feedback for
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};
