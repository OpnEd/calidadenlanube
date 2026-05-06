<?php

namespace App\Models;

use App\Enums\RoleType;
use App\Models\Quality\Training\Enrollment;
use App\Traits\MultiTenantHasRoles;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasTeamRoles;
use App\Traits\HasTeamAuthorization;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification;

class User extends Authenticatable implements FilamentUser, HasTenants, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable,
        HasRoles,
        HasTeamRoles,
        HasTeamAuthorization;
    //MultiTenantHasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_surgeon',
        'data',
    ];

    protected $casts = [
        'is_surgeon' => 'boolean',
        'data' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data' => 'array',
        ];
    }

    /**
     * Determine if the user can access the given Filament panel.
     *
     * @param \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        /* $team = Filament::getTenant();

        if (! $team) {
            Log::warning("No tenant resolved for team-scoped role access.");
            return false;
        } */

        if ($panel->getId() === 'tenantManager') {
            return $this->roles()
                ->where('name', 'Super Admin')
                ->whereNull('model_has_roles.team_id')
                ->exists();
        }

        if ($panel->getId() === 'admin') {
            // Obtiene todos los dominios de email registrados en la tabla users
            $domains = self::query()
                ->selectRaw("DISTINCT SUBSTRING_INDEX(email, '@', -1) as domain")
                ->pluck('domain')
                ->toArray();

            foreach ($domains as $domain) {
                if (str_ends_with($this->email, '@' . $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function notifications()
    {
        $team = Filament::getTenant();

        $query = $this->morphMany(DatabaseNotification::class, 'notifiable');

        if ($team) {
            $query->where('team_id', $team->id);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Sobrescribir el método readNotifications para filtrar por tenant
     */
    public function readNotifications(): MorphMany
    {
        $team = Filament::getTenant();
        
        $query = $this->morphMany(DatabaseNotification::class, 'notifiable')
                     ->whereNotNull('read_at')
                     ->orderBy('created_at', 'desc');
        
        if ($team) {
            $query->where('team_id', $team->id);
        }
        
        return $query;
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relación: Un usuario pertenece a un equipo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
    }

    /**
     * Sobrescribir el método unreadNotifications para filtrar por tenant
     */
    public function unreadNotifications(): MorphMany
    {
        $team = Filament::getTenant();
        
        $query = $this->morphMany(DatabaseNotification::class, 'notifiable')
                     ->whereNull('read_at')
                     ->orderBy('created_at', 'desc');
        
        if ($team) {
            $query->where('team_id', $team->id);
        }
        
        return $query;
    }

    public function user_answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
}

/* $teamId = session('team_id'); // ⚠️ Este es el valor que se usa para filtrar roles

        if (!$teamId) {
            return false; // Si no hay team en sesión, denegamos acceso
        }

        $team = Team::find($teamId); // O el modelo que uses para equipos

        if (! $team) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            //return $this->hasAnyRole(RoleType::values(), $team);
            return $this->hasAnyRole(array_map(fn($case) => $case->value, RoleType::cases()), $team);

        }

        if ($panel->getId() === 'tenantManager') {
            return $this->hasRole(RoleType::SUPERADMIN->value, $team);
        }

        return false; */
        /* $team = Team::find(session('team_id'));

        if (! $team) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(RoleType::values(), $team),
            'tenantManager' => $this->hasRole(RoleType::SUPERADMIN->value, $team),
            default => false,
        }; */
        /* $teamId = session('team_id');
        if (! $teamId) {
            return false;
        }

        $team = Team::find($teamId);
        if (! $team) {
            return false;
        } */
        /*  $team = 1;
        return match ($panel->getId()) {
             'admin' => $this->hasRole([
                'super-admin',
                'admin',
                'director',
                'medico',
                'cliente',
                'comercial',
                'auxiliar-vet',
                'auxiliar-bodega'
            ], $team),
            'tenantManager' => $this->hasRole('super-admin', $team),
            default => false,
        };*/

        /* $team = Filament::getTenant(); // Obtener tenant actual desde tu lógica (ej: session, subdominio, etc.)

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(
                $team, // ← Team/tenant actual
                [
                    'super-admin',
                    'admin',
                    'director',
                    'medico',
                    'cliente',
                    'comercial',
                    'auxiliar-vet',
                    'auxiliar-bodega'
                ]
            ),
            'tenantManager' => $this->hasRole('super-admin', $team), // ← Team como segundo parámetro
            default => false
        }; */
