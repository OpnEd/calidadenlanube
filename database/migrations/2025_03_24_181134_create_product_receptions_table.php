<?php

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Team;
use App\Models\User;
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
        Schema::create('product_receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Purchase::class)->constrained();
            $table->foreignIdFor(Invoice::class)->constrained();
            $table->enum('status', ['in_progress', 'done', 'rejected'])->default('done');
            $table->timestamp('reception_date')->nullable();
            $table->text('observations')->nullable();
            $table->json('data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_receptions');
    }
};
