<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'sale_id', // Relación con Venta que incluye la relación con el cliente
        'dispatch_id', // Relación con Despacho que incluye la relación con el proveedor
        'code',
        'amount',
        'is_our', // el modelo Invoice almacena todas las facturas, incluidas las que vienen de terceros
        'supplier_id',
        'issued_date',
        'data',
    ];

    protected $casts = [
        'issued_date' => 'datetime',
        'is_our' => 'boolean',
        'amount' => 'decimal:2',
        'data' => 'array',
    ];
    
    protected static function booted()
    {
        static::creating(function (Invoice $invoice) {
            // Si no está explícito en $invoice->team_id
            if (empty($invoice->team_id)) {
                $tenant = Filament::getTenant();
                $invoice->team_id = $tenant ? $tenant->id : null;
            }
        });
    }

    public function reception(): HasOne
    {
        return $this->hasOne(ProductReception::class);
    }

    public function generateCode($sale): string
    {
        // Genera un código único para la factura, por ejemplo, usando un prefijo y un timestamp
        return 'INV-' . now()->format('Ymd-His') . '-' . $sale->id;
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Relación inversa con Venta
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Relación inversa con Despacho 
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

}
