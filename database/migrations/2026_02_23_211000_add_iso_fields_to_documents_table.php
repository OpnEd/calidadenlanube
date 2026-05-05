<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('code')->nullable()->after('team_id');
            $table->foreignIdFor(User::class, 'owner_user_id')
                ->nullable()
                ->after('document_category_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status')->default('draft')->after('slug');
            $table->date('effective_at')->nullable()->after('status');
            $table->date('expires_at')->nullable()->after('effective_at');
            $table->boolean('is_obsolete')->default(false)->after('expires_at');
            $table->unsignedBigInteger('current_version_id')->nullable()->after('is_obsolete');
            $table->index(['team_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropIndex(['team_id', 'code']);
            $table->dropColumn([
                'code',
                'owner_user_id',
                'status',
                'effective_at',
                'expires_at',
                'is_obsolete',
                'current_version_id',
            ]);
        });
    }
};
