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
        Schema::table('recipebooks', function (Blueprint $table) {
            $table->enum('status', ['available', 'in_use', 'used'])->default('available')->after('signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipebooks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
