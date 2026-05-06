<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Drop primary key and the team index
        DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY');
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('model_has_roles_team_foreign_key_index');
        });

        // 2) Change column to nullable (requires doctrine/dbal)
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->change();
        });

        // 3) Optionally add FK with ON DELETE SET NULL
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
        });

        // 4) Recreate primary key without team_id
        DB::statement('ALTER TABLE `model_has_roles` ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: drop new PK, drop FK, make team_id not null, restore index and original PK
        DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY');

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable(false)->change();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index('team_id', 'model_has_roles_team_foreign_key_index');
        });

        DB::statement('ALTER TABLE `model_has_roles` ADD PRIMARY KEY (`team_id`,`role_id`,`model_id`,`model_type`)');
    }
};
