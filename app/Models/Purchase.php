<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Filament\Facades\Filament;
use App\Enums\PurchaseStatus;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'supplier_id',
        'code',
        'status',
        'total', // se actualiza mediante observer luego de guardar cambios en la orden de compra
        'observations',
        'data',
    ];

    protected $casts = [
        'status' => PurchaseStatus::class,
        'total' => 'decimal:2',
        'data' => 'array',
    ];

    protected static function booted()
    {
        static::updated(function ($purchase) {
            if ($purchase->isDirty('total')) {
                Cache::forget("purchase_{$purchase->id}_total");
            }
        });
    }

    public function dispatch(): HasOne
    {
        return $this->hasOne(Dispatch::class);
    }
    
    public function generatePurchaseCode(): string
    {
        $teamId = Filament::getTenant()->id;
        $lastSale = self::where('team_id', $teamId)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastCode = $lastSale ? (int) substr($lastSale->code, -4) : 0;

        return 'OC' . str_pad($lastCode + 1, 4, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function reception(): HasOne
    {
        return $this->hasOne(ProductReception::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /* public function updatePurchaseTotal(): void
    {
        $this->total = $this->items()->sum('total');
        $this->save();
    } */

    public function updatePurchaseTotal(): void
    {
        // Actualizar usando sum() de Eloquent para mejor performance
        /* $this->update([
            'total' => $this->items()->sum('total')
        ]);

        // Opcional: Si necesitas recargar la instancia
        $this->refresh(); */

        $total = $this->items()->sum('total');

        $this->total = $total;
        $this->save();

        $this->refresh();

        Cache::put("purchase_{$this->id}_total", $total, 3600);
    }
}
