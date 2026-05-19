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
        // Renombrar la tabla
        Schema::rename('invoice_item', 'invoice_items');

        // Renombrar constraints que apuntaban a la tabla anterior

        DB::statement("ALTER TABLE invoice_items DROP FOREIGN KEY invoice_item_invoice_id_foreign");
        DB::statement("ALTER TABLE invoice_items ADD CONSTRAINT invoice_items_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES invoices(id)");

        // Renombrar constraints que apuntaban a la tabla anterior
        DB::statement("ALTER TABLE invoice_items DROP FOREIGN KEY invoice_item_sale_item_id_foreign");
        DB::statement("ALTER TABLE invoice_items ADD CONSTRAINT invoice_items_sale_item_id_foreign FOREIGN KEY (sale_item_id) REFERENCES sale_items(id)");

        // Renombrar constraints que apuntaban a la tabla anterior
        DB::statement("ALTER TABLE invoice_items DROP FOREIGN KEY invoice_item_batch_id_foreign");
        DB::statement("ALTER TABLE invoice_items ADD CONSTRAINT invoice_items_batch_id_foreign FOREIGN KEY (batch_id) REFERENCES batches(id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('invoice_items', 'invoice_item');
        // Revertir constraints
        DB::statement("ALTER TABLE invoice_item DROP FOREIGN KEY invoice_items_invoice_id_foreign");
        DB::statement("ALTER TABLE invoice_item ADD CONSTRAINT invoice_item_invoice_id_foreign FOREIGN KEY (invoice_id) REFERENCES invoices(id)");
        DB::statement("ALTER TABLE invoice_item DROP FOREIGN KEY invoice_items_sale_item_id_foreign");
        DB::statement("ALTER TABLE invoice_item ADD CONSTRAINT invoice_item_sale_item_id_foreign FOREIGN KEY (sale_item_id) REFERENCES sale_items(id)");
        DB::statement("ALTER TABLE invoice_item DROP FOREIGN KEY invoice_items_batch_id_foreign");
        DB::statement("ALTER TABLE invoice_item ADD CONSTRAINT invoice_item_batch_id_foreign FOREIGN KEY (batch_id) REFERENCES batches(id)");
    }
};
