<?php

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Document::class)->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->nullable()->constrained('document_versions')->nullOnDelete();
            $table->string('scope_type')->default('all');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->boolean('required_read')->default(true);
            $table->foreignIdFor(User::class, 'distributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('distributed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_distributions');
    }
};

