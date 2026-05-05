<?php

namespace App\Filament\Pages\Tenancy;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Dom\Text;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre de la Droguería')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $base = Str::slug($state ?? '');
                        $set('slug', $this->makeCompanySlugUnique($base, $get('identification'), $get('id')));
                    }),

                TextInput::make('identification')
                    ->label('NIT')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $base = Str::slug($get('name') ?? '');
                        $set('slug', $this->makeCompanySlugUnique($base, $state, $get('id')));
                    }),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('address')
                    ->label('Dirección')
                    ->required(),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),

                TextInput::make('phonenumber')
                    ->label('Teléfono (fijo o celular)')
                    ->tel()
                    ->required(),
            ]);
    }

    /**
     * Ajusta el slug para que quede único:
     * - Si no existe, usa $base.
     * - Si ya existe, agrega "-####" donde #### son los últimos 4 de identification.
     * - Si aún choca, agrega "-1", "-2", ...
     */
    protected function makeCompanySlugUnique(string $base, ?string $identification, $ignoreId = null): string
    {
        $base = $base ?: 'drogueria';

        // Últimos 4 dígitos del NIT (solo números).
        $digits = preg_replace('/\D+/', '', (string) $identification);
        $suffix4 = $digits ? substr($digits, -4) : null;

        // OJO: ajusta el Model y columna según tu app.
        $query = \App\Models\Team::query();

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        // 1) probar base
        if (! (clone $query)->where('slug', $base)->exists()) {
            return $base;
        }

        // 2) probar base-#### si hay identification
        $candidate = $suffix4 ? "{$base}-{$suffix4}" : $base;

        if (! (clone $query)->where('slug', $candidate)->exists()) {
            return $candidate;
        }

        // 3) fallback incremental
        $i = 1;
        while ((clone $query)->where('slug', "{$candidate}-{$i}")->exists()) {
            $i++;
        }

        return "{$candidate}-{$i}";
    }

    protected function handleRegistration(array $data): Team
    {
        return DB::transaction(function () use ($data) {

            $team = $this->createTeam($data);

            $team->users()->attach(\Illuminate\Support\Facades\Auth::user());

            [$roleAdmin, $roleDirTecn] = $this->createRoles($team);

            // Configura Spatie para que trabaje por team
            app(PermissionRegistrar::class)->setPermissionsTeamId($team->id);

            $this->assignRoles($team, Auth::user(), $roleAdmin);

            $this->createPermissionsAndSyncRole($team, $roleAdmin);

            return $team;
        });
    }

    private function createTeam(array $data): Team
    {
        // Validaciones extra opcionales
        return Team::create($data);
    }

    /**
     * Crea (o recupera) roles scoped al team.
     * @return array [Role $adminRole, Role $consultantRole]
     */
    private function createRoles(Team $team): array
    {
        $admin = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web', 'team_id' => $team->id]
        );

        $dirTecn = Role::firstOrCreate(
            ['name' => 'Director', 'guard_name' => 'web', 'team_id' => $team->id]
        );

        return [$admin, $dirTecn];
    }

    private function assignRoles(Team $team, User $user, Role $adminRole): void
    {
        // Asignar roles sobre modelos (no sobre IDs)
        $user->assignRole($adminRole);
    }

    /**
     * Crea permisos base y sincroniza al rol administrador.
     */
    private function createPermissionsAndSyncRole(Team $team, Role $roleAdmin): void
    {
        $permissionNames = [];

        foreach (PermissionType::cases() as $permissionType) {
            // firstOrCreate por team_id si tu tabla la tiene
            $perm = Permission::firstOrCreate([
                'name'       => $permissionType->value,
                'guard_name' => 'web',
                'team_id'    => $team->id,
            ]);

            if (array_key_exists('label', $perm->getAttributes())) {
                $perm->label = $permissionType->getLabel();
                $perm->save();
            }

            $permissionNames[] = $perm->name;
        }

        $roleAdmin->syncPermissions($permissionNames);
    }
}
