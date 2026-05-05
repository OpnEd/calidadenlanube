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
        Schema::create('quality_improvement_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignId('finding_id')->nullable()->constrained('quality_improvement_findings')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('quality_improvement_plans')->nullOnDelete();
            $table->foreignId('action_id')->nullable()->constrained('quality_improvement_actions')->nullOnDelete();
            $table->foreignIdFor(User::class, 'uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_improvement_evidences');
    }
};

