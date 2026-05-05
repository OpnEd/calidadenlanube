<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que se asignarán de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'identification',
        'address',
        'email',
        'phonenumber',
        'data',
        'slug'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function anesthesiaSheets(): HasMany
    {
        return $this->hasMany(AnesthesiaSheet::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function checklist_items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function checklist_item_answers(): HasMany
    {
        return $this->hasMany(ChecklistItemAnswer::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    public function document_categories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    public function environmentalRecords(): HasMany
    {
        return $this->hasMany(EnvironmentalRecord::class);
    }

    public function improvement_plans(): HasMany
    {
        return $this->hasMany(ImprovementPlan::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function kardexEntries(): HasMany
    {
        return $this->hasMany(KardexEntry::class);
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function pets(): HasManyThrough
{
    // hasManyThrough(FinalModel, ThroughModel, foreignKeyOnThrough, foreignKeyOnFinal, localKey, localKeyOnThrough)
    // Team.id -> customers.team_id
    // customers.id -> pets.customer_id
    return $this->hasManyThrough(
        Pet::class,
        Customer::class,
        'team_id',     // foreign key on customers table...
        'customer_id', // foreign key on pets table...
        'id',          // local key on teams table...
        'id'           // local key on customers table...
    );
}

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(TeamProductPrice::class);
    }

    public function productReceptions(): HasMany
    {
        return $this->hasMany(ProductReception::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function recipebooks(): HasMany
    {
        return $this->hasMany(Recipebook::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function training_categories(): HasMany
    {
        return $this->hasMany(TrainingCategory::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * Relación: Un equipo tiene muchos usuarios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
