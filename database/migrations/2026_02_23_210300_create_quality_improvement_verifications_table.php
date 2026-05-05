<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_improvement_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('quality_improvement_plans')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('result')->default('parcial');
            $table->decimal('before_value', 10, 2)->nullable();
            $table->decimal('after_value', 10, 2)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_improvement_verifications');
    }
};

