<?php

use App\Models\Process;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_improvement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignId('finding_id')->nullable()->constrained('quality_improvement_findings')->nullOnDelete();
            $table->foreignIdFor(Process::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->nullable()->index();
            $table->text('objective');
            $table->text('scope')->nullable();
            $table->string('priority')->default('media');
            $table->string('status')->default('borrador');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('baseline_value', 10, 2)->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->text('expected_impact')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_improvement_plans');
    }
};

