<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Sale;
use App\Models\SaleItem;

class InvoiceService
{
    /**
     * Genera la factura basándose en la venta y los items de la venta.
     */
    public function generateInvoice($sale, $saleItems)
    {
        $invoiceCode = (new Invoice())->generateCode($sale);
        
        $invoice = Invoice::create([
            'team_id'     => $sale->team_id,
            'sale_id'     => $sale->id,
            'supplier_id' => null,
            'code'        => $invoiceCode,
            'amount'      => $sale->total,
            'is_our'      => true,
            'issued_date' => now()->toDateString(),
            'data'        => null,
        ]);
        
        foreach ($saleItems as $saleItem) {

            $batchId = $inventory?->batch_id ?? null;

            InvoiceItem::create([
                'invoice_id'   => $invoice->id,
                'sale_item_id' => $saleItem->id,
                'batch_id'     => $batchId,
                'due_date'     => null,
                'quantity'     => $saleItem->quantity,
                'price'        => $saleItem->sale_price,
                'total'        => $saleItem->total,
            ]);

        }
        
        return $invoice;
    }

}
