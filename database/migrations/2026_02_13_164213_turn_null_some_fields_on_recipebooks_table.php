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
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->date('issue_date')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('patient_id')->nullable()->change();
            $table->string('diagnosis', 255)->nullable()->change();
            $table->text('signature')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipebooks', function (Blueprint $table) {            
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->date('issue_date')->nullable(false)->change();
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
            $table->string('diagnosis', 255)->nullable(false)->change();
            $table->text('signature')->nullable(false)->change();
        });
    }
};
