<?php

namespace App\Services\Quality;

use App\Models\Quality\Training\AuditLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action to the audit trail
     */
    public static function log(
        Team|int|null $team,
        string $resourceType,
        int|string $resourceId,
        string $action,
        ?User $user = null,
        ?array $changes = null,
        ?string $description = null,
    ): ?AuditLog {
        $team = self::resolveTeam($team);

        if (! $team) {
            return null;
        }

        $user = $user ?? Auth::user();

        return AuditLog::create([
            'team_id' => $team->id,
            'user_id' => $user?->id,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => $action,
            'changes' => $changes,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a create action
     */
    public static function logCreate(
        Team|int|null $team,
        string $resourceType,
        int|string $resourceId,
        ?User $user = null,
        ?string $description = null,
    ): ?AuditLog {
        return self::log(
            $team,
            $resourceType,
            $resourceId,
            AuditLog::ACTION_CREATE,
            $user,
            null,
            $description ?? "$resourceType #$resourceId created",
        );
    }

    /**
     * Log an update action
     */
    public static function logUpdate(
        Team|int|null $team,
        string $resourceType,
        int|string $resourceId,
        array $oldValues,
        array $newValues,
        ?User $user = null,
        ?string $description = null,
    ): ?AuditLog {
        // Only track fields that actually changed
        $changes = [];
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = $newValue;
            }
        }

        return self::log(
            $team,
            $resourceType,
            $resourceId,
            AuditLog::ACTION_UPDATE,
            $user,
            [
                'old' => $oldValues,
                'new' => $newValues,
            ],
            $description ?? "$resourceType #$resourceId updated",
        );
    }

    /**
     * Log a delete action
     */
    public static function logDelete(
        Team|int|null $team,
        string $resourceType,
        int|string $resourceId,
        ?User $user = null,
        ?string $description = null,
    ): ?AuditLog {
        return self::log(
            $team,
            $resourceType,
            $resourceId,
            AuditLog::ACTION_DELETE,
            $user,
            null,
            $description ?? "$resourceType #$resourceId deleted",
        );
    }

    /**
     * Log a read/view action
     */
    public static function logRead(
        Team|int|null $team,
        string $resourceType,
        int|string $resourceId,
        ?User $user = null,
        ?string $description = null,
    ): ?AuditLog {
        return self::log(
            $team,
            $resourceType,
            $resourceId,
            AuditLog::ACTION_READ,
            $user,
            null,
            $description ?? "$resourceType #$resourceId viewed",
        );
    }

    /**
     * Log an export action
     */
    public static function logExport(
        Team|int|null $team,
        string $resourceType,
        int $count,
        ?User $user = null,
        ?string $description = null,
    ): ?AuditLog {
        return self::log(
            $team,
            $resourceType,
            0, // No specific resource
            AuditLog::ACTION_EXPORT,
            $user,
            ['count' => $count],
            $description ?? "$resourceType exported ($count records)",
        );
    }

    /**
     * Get audit logs for a resource
     */
    public static function getResourceLogs(string $resourceType, int|string $resourceId, ?Team $team = null): array
    {
        $query = AuditLog::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->orderByDesc('created_at');

        if ($team) {
            $query->where('team_id', $team->id);
        }

        return $query->get()->toArray();
    }

    /**
     * Get user's recent actions
     */
    public static function getUserActions(User $user, int $limit = 50): array
    {
        return AuditLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get team's activity summary
     */
    public static function getTeamActivitySummary(Team $team, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_actions' => AuditLog::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'by_action' => AuditLog::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $startDate)
                ->groupBy('action')
                ->selectRaw('action, count(*) as count')
                ->get()
                ->pluck('count', 'action')
                ->toArray(),
            'by_resource' => AuditLog::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $startDate)
                ->groupBy('resource_type')
                ->selectRaw('resource_type, count(*) as count')
                ->get()
                ->pluck('count', 'resource_type')
                ->toArray(),
            'by_user' => AuditLog::query()
                ->where('team_id', $team->id)
                ->where('created_at', '>=', $startDate)
                ->with('user')
                ->groupBy('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->get()
                ->map(fn ($log) => [
                    'user' => $log->user?->name ?? 'Unknown',
                    'count' => $log->count,
                ])
                ->toArray(),
        ];
    }

    private static function resolveTeam(Team|int|null $team): ?Team
    {
        if ($team instanceof Team) {
            return $team->exists ? $team : Team::query()->find($team->id);
        }

        if ($team === null) {
            return null;
        }

        return Team::query()->find($team);
    }
}
