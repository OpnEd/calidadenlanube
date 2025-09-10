<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'identification',
        'address',
        'email',
        'phonenumber',
        'data'
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
    
    protected static function booted()
    {
        static::creating(function (Customer $customer) {
            // Si no está explícito en $customer->team_id
            if (empty($customer->team_id)) {
                $tenant = Filament::getTenant();
                $customer->team_id = $tenant ? $tenant->id : null;
            }
        });
    }

    /**
     * Crea y retorna un Customer genérico.
     *
     * @param array $attributes
     * @return static
     */
    public static function createGeneric(array $attributes = []): self
    {
        $defaults = [
            'name' => 'Cliente Genérico',
            'identification' => '88888888',
            'address' => 'Sin dirección',
            'email' => 'generico@example.com',
            'phonenumber' => '9999999999',
            'data' => [],
        ];

        $data = array_merge($defaults, $attributes);

        return self::create($data);
    }

    // Relación uno a muchos con Factura
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    // Relación uno a muchos con Venta
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
