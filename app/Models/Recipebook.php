<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipebook extends Model
{
    use SoftDeletes;
    
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_IN_USE = 'in_use';

    protected $fillable = [
        'consecutive',
        'issue_date',
        'team_id',
        'user_id', // Médico que diligencia
        'customer_id', // Propietario de mascota
        'patient_id', // Paciente o mascota
        'diagnosis',
        'status', // available, in_use, used
        'signature',
    ];
    
    public function anesthesiaSheet()
    {
        return $this->hasOne(AnesthesiaSheet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recipebook_items(): HasMany
    {
        return $this->hasMany(RecipebookItem::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ======= SCOPES =======

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeForBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }
    
    // ======= MÉTODOS =======

    public function markAsInUse()
    {
        $this->update([
            'status' => 'in_use',
        ]);
    }

    public function markAsUsed()
    {
        $this->update([
            'status' => 'used',
        ]);
    }

    public function canBeAssigned(): bool
    {
        return $this->status === 'available';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isInUse(): bool
    {
        return $this->status === 'in_use';
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }
}
