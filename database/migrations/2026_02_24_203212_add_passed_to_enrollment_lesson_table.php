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
        Schema::table('enrollment_lesson', function (Blueprint $table) {
            $table->boolean('passed')->default(false)->after('last_accessed_at');
            $table->timestamp('passed_at')->nullable()->after('passed');
            $table->decimal('score', 5, 2)->nullable()->after('passed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_lesson', function (Blueprint $table) {
            $table->dropColumn('passed');
            $table->dropColumn('passed_at');
            $table->dropColumn('score');
        });
    }
};
