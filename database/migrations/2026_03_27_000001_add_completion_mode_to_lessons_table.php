<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lessons')) {
            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'completion_mode')) {
                $column = Schema::hasColumn('lessons', 'iframe') ? 'iframe' : 'video_url';

                $table->string('completion_mode', 32)
                    ->default('assessment_required')
                    ->after($column);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lessons') || ! Schema::hasColumn('lessons', 'completion_mode')) {
            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('completion_mode');
        });
    }
};
