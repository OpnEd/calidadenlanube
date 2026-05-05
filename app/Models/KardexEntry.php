<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class KardexEntry extends Model
{
    use SoftDeletes;
    
    protected $table = 'kardex_entries';

    protected $fillable = [
        'team_id',
        'inventory_id',
        'anesthesia_sheet_id',
        'anesthesia_sheet_item_id',
        'recipebook_id',
        'movement_date',
        'movement_type',      // in, out, adjust
        'quantity',
        'unit',
        'stock_before',
        'stock_after',
        'notes',
        'reference_kardex_entry_id',
        'adjustment_reason',
        'model_version_id',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity'      => 'decimal:2',
        'stock_before'  => 'decimal:2',
        'stock_after'   => 'decimal:2',
    ];

    public function anesthesiaSheet(): BelongsTo
    {
        return $this->belongsTo(AnesthesiaSheet::class);
    }

    public function anesthesiaSheetItem(): BelongsTo
    {
        return $this->belongsTo(AnesthesiaSheetItem::class);
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(KardexEntry::class, 'reference_kardex_entry_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(KardexEntry::class, 'reference_kardex_entry_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function recipebook(): BelongsTo
    {
        return $this->belongsTo(Recipebook::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

}
