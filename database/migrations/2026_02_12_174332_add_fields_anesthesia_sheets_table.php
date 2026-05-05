<?php

use App\Models\Recipebook;
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
        Schema::table('anesthesia_sheets', function (Blueprint $table) {
           // $table->foreignIdFor(Recipebook::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete()->unique()->nullable(false)->after('id');
           // $table->foreignId('pet_id')->nullable()->constrained('pets');
           // $table->enum('status', ['opened', 'closed', 'canceled'])->default('opened'); // opened, closed
           // $table->boolean('consumed')->default(false); // Indicates if the anesthesia sheet has been consumed (used for billing or inventory purposes)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anesthesia_sheets', function (Blueprint $table) {
            //$table->dropForeign(['recipebook_id']);
            //$table->dropColumn('recipebook_id');
            //$table->dropForeign(['pet_id']);
            //$table->dropColumn('pet_id');
            //$table->dropColumn('status');
            //$table->dropColumn('consumed');
        });
    }
};
