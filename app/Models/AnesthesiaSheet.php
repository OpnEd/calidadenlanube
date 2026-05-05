<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnesthesiaSheet extends Model
{
    /** @use HasFactory<\Database\Factories\AnesthesiaSheetFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'team_id',
        'user_id',
        'recipebook_id',
        'customer_id',
        'pet_id',
        'surgeon_id',
        'anamnesis',
        'anesthesia_notes',
        'anesthesia_start_time',
        'anesthesia_end_time',
        'status', // opened, closed
        'consumed' // Indicates if the anesthesia sheet has been consumed (used for billing or inventory purposes)
    ];

    protected $casts = [
        'anesthesia_notes' => 'array',
        'anamnesis' => 'array',
        'anesthesia_start_time' => 'datetime',
        'anesthesia_end_time' => 'datetime',
        'consumed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function anesthesiaItems(): HasMany
    {
        return $this->hasMany(AnesthesiaSheetItem::class);
    }

    public function versions(): MorphMany
    {
        return $this->morphMany(ModelVersion::class, 'versionable')
            ->latest();
    }


    public static function generateAnesthesiaSheetConsecutive(): string
    {
        $lastSheet = self::where('team_id', Filament::getTenant()->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastNumber = $lastSheet ? (int) substr($lastSheet->code, -4) : 0;

        return 'AS' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function kardexEntries(): HasMany
    {
        return $this->hasMany(KardexEntry::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function recipebook(): BelongsTo
    {
        return $this->belongsTo(Recipebook::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->where('is_surgeon', true);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
