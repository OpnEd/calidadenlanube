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
        Schema::create('quality_improvement_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Process::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('otro');
            $table->string('severity')->default('media');
            $table->string('status')->default('abierto');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_improvement_findings');
    }
};

