<?php

use App\Models\Manufacturer;
use App\Models\SanitaryRegistry;
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
        Schema::create('central_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SanitaryRegistry::class)->constrained();
            $table->foreignIdFor(Manufacturer::class);
            $table->string('code')->unique();
            $table->date('manufacturing_date');
            $table->date('expiration_date');
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
        Schema::dropIfExists('central_batches');
    }
};
