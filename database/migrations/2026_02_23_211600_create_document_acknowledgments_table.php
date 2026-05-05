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
        Schema::create('document_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Document::class)->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->nullable()->constrained('document_versions')->nullOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->boolean('required')->default(true);
            $table->string('status')->default('pending');
            $table->date('due_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'document_version_id', 'user_id'], 'doc_ack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_acknowledgments');
    }
};

