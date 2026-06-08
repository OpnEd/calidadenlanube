<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\AuditLogFactory> */
    use HasFactory;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_READ = 'read';
    public const ACTION_EXPORT = 'export';

    protected $fillable = [
        'team_id',
        'user_id',
        'resource_type',
        'resource_id',
        'action',
        'changes',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the team this audit log belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class);
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => 'Creado',
            self::ACTION_UPDATE => 'Actualizado',
            self::ACTION_DELETE => 'Eliminado',
            self::ACTION_READ => 'Consultado',
            self::ACTION_EXPORT => 'Exportado',
            default => $this->action,
        };
    }

    /**
     * Get resource name for display
     */
    public function getResourceNameAttribute(): string
    {
        return match ($this->resource_type) {
            'Course' => 'Curso',
            'Module' => 'Módulo',
            'Lesson' => 'Lección',
            'Enrollment' => 'Matrícula',
            'Certificate' => 'Certificado',
            'Assessment' => 'Evaluación',
            'User' => 'Usuario',
            default => $this->resource_type,
        };
    }

    /**
     * Get old values from changes
     */
    public function getOldValuesAttribute(): array
    {
        return $this->changes['old'] ?? [];
    }

    /**
     * Get new values from changes
     */
    public function getNewValuesAttribute(): array
    {
        return $this->changes['new'] ?? [];
    }

    /**
     * Get list of changed fields
     */
    public function getChangedFieldsAttribute(): array
    {
        $old = $this->getOldValuesAttribute();
        $new = $this->getNewValuesAttribute();

        return array_keys(array_merge($old, $new));
    }

    /**
     * Check if a specific field was changed
     */
    public function fieldWasChanged(string $field): bool
    {
        return in_array($field, $this->getChangedFieldsAttribute());
    }

    /**
     * Get the change for a specific field
     */
    public function getFieldChange(string $field): ?array
    {
        $old = $this->getOldValuesAttribute();
        $new = $this->getNewValuesAttribute();

        if (!isset($old[$field]) && !isset($new[$field])) {
            return null;
        }

        return [
            'old' => $old[$field] ?? null,
            'new' => $new[$field] ?? null,
        ];
    }

    /**
     * Scope: filter by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: filter by resource type
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: recent logs (last N days)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
