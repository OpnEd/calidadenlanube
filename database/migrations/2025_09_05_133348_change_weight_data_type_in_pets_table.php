<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->decimal('weight', 5, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->smallInteger('weight')->nullable()->change();
        });
    }

};
