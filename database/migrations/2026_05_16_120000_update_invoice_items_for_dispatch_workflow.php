<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['sale_item_id']);
            $table->dropForeign(['batch_id']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_item_id')->nullable()->change();
            $table->unsignedBigInteger('batch_id')->nullable()->change();
            $table->foreignId('central_batch_id')
                ->nullable()
                ->after('batch_id');
            $table->foreignId('dispatch_item_id')
                ->nullable()
                ->after('central_batch_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('sale_item_id')
                ->references('id')
                ->on('sale_items')
                ->cascadeOnDelete();
            $table->foreign('batch_id')
                ->references('id')
                ->on('batches')
                ->cascadeOnDelete();
            $table->foreign('central_batch_id')
                ->references('id')
                ->on('central_batches')
                ->nullOnDelete();
            $table->foreign('dispatch_item_id')
                ->references('id')
                ->on('dispatch_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['central_batch_id']);
            $table->dropForeign(['dispatch_item_id']);
            $table->dropForeign(['sale_item_id']);
            $table->dropForeign(['batch_id']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('central_batch_id');
            $table->dropColumn('dispatch_item_id');
            $table->unsignedBigInteger('sale_item_id')->nullable(false)->change();
            $table->unsignedBigInteger('batch_id')->nullable(false)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('sale_item_id')
                ->references('id')
                ->on('sale_items')
                ->cascadeOnDelete();
            $table->foreign('batch_id')
                ->references('id')
                ->on('batches')
                ->cascadeOnDelete();
        });
    }
};
