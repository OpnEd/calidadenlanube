<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', //categoría del producto
        'pharmaceutical_form_id', //forma farmacéutica, si aplica
        'bar_code', //código
        'name', //nombre comercial
        'drug', //principio activo
        'unit_measurement', //unidad de medida del producto
        'drug_concentration', //concentración del principio activo por unidad de medida del producto
        'recomended_dose', //dosis recomendada
        'description', //presentación comercial
        'is_high_risk', //indica si es de alto riesgo
        'is_mce', //indica si es un medicamento de control especial
        'fractionable', //fraccionable
        'conversion_factor', //factor de conversión
        'min_fraction', //tamaño minimo de fraccion consumible
        'image', // imagen
        'tax', //impuesto
        'status' //estado:activo o inactivo
    ];

    protected function casts(): array
    {
        return [
            'code' => 'string',
            'name' => 'string',
            'drug' => 'string',
            'description' => 'string',
            'is_high_risk' => 'boolean',
            'is_mce' => 'boolean',
            'fractionable' => 'boolean',
            'conversion_factor' => 'decimal:2',
            'min_fraction' => 'decimal:4',
            'tax' => 'decimal:2',
            'image' => 'string',
            'status' => 'boolean'
        ];
    }

    public function product_category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product_reception_items(): HasMany
    {
        return $this->hasMany(ProductReceptionItem::class);
    }

    public function pharmaceutical_form(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalForm::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(TeamProductPrice::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Scope: productos con stock total > 0
     * Lo que hace este scope es agregar un 
     * WHERE EXISTS (SELECT 1 FROM stocks … HAVING SUM(quantity)>0) 
     * al query de productos.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereExists(function ($subquery) {
            $subquery->from('stocks')
                ->select(DB::raw('1')) // importante: NO SELECT *
                ->whereColumn('stocks.product_id', 'products.id')
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0');
        });
        // O también se puede hacer así:
        /* return $query->whereIn('id', function ($subquery) {
            $subquery->from('stocks')
                ->select('product_id')
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0');
        }); */
    }

    /**
     * Scope: productos con inventory total > 0
     * Lo que hace este scope es agregar un 
     * WHERE EXISTS (SELECT 1 FROM stocks … HAVING SUM(quantity)>0) 
     * al query de productos.
     */
    public function scopeInInventory(Builder $query): Builder
    {
        return $query->whereExists(function ($subquery) {
            $subquery->from('inventories')
                ->select(DB::raw('1')) // importante: NO SELECT *
                ->whereColumn('inventories.product_id', 'products.id')
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0');
        });
    }
    /**
     * Relación con PeripheralProductPrice
     * Obtiene el precio del producto para el equipo actual.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function peripheralPrice(): HasOne
    {
        $teamId = Filament::getTenant()->id;
        return $this
            ->hasOne(PeripheralProductPrice::class)
            ->where('team_id', $teamId);
    }
}
