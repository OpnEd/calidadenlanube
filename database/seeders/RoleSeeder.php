<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Enums\RoleType;
use App\Enums\PermissionType;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para un team específico
     */
    public function run(int $teamId): void
    {
        // Establecer el guard y el team context
        $guardName = 'web';
        
        // Obtener todos los permisos disponibles
        $allPermissions = PermissionType::cases();
        
        // Crear/sincronizar permisos en la BD (solo si no existen)
        foreach ($allPermissions as $permissionEnum) {
            Permission::firstOrCreate(
                ['name' => $permissionEnum->value, 'guard_name' => $guardName],
                ['description' => $permissionEnum->getLabel()]
            );
        }
        
        // Definir qué permisos tiene cada rol
        $rolePermissions = $this->defineRolePermissions();
        
        // Crear roles y asignar permisos para este team
        foreach (RoleType::cases() as $roleEnum) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleEnum->value,
                    'guard_name' => $guardName,
                    'team_id' => $teamId, // Importante para multitenant
                ],
                ['description' => $roleEnum->getLabel()]
            );
            
            // Asignar permisos al rol
            $permissions = $rolePermissions[$roleEnum->value] ?? [];
            $role->syncPermissions($permissions);
        }
    }
    
    /**
     * Define qué permisos tiene cada rol
     */
    private function defineRolePermissions(): array
    {
        return [
            RoleType::ADMIN->value => [
                // Admin tiene TODOS los permisos
                ...array_map(fn($p) => $p->value, PermissionType::cases()),
            ],
            
            RoleType::DIRECTOR->value => [
                PermissionType::VIEW_ANESTHESIA_SHEET->value,
                PermissionType::VIEW_BATCH->value,
                PermissionType::EDIT_BATCH->value,
                PermissionType::CREATE_BATCH->value,
                // ... más permisos según necesites
            ],
            
            RoleType::MEDICO->value => [
                PermissionType::VIEW_ANESTHESIA_SHEET->value,
                PermissionType::CREATE_ANESTHESIA_SHEET->value,
                PermissionType::EDIT_ANESTHESIA_SHEET->value,
                PermissionType::VIEW_BATCH->value,
                // ... según necesites
            ],
            
            RoleType::CLIENTE->value => [
                PermissionType::VIEW_BATCH->value,
                // Permisos limitados
            ],
            
            RoleType::COMERCIAL->value => [
                PermissionType::VIEW_BATCH->value,
                PermissionType::EDIT_BATCH->value,
                // ... según necesites
            ],
            
            RoleType::AUXILIARVET->value => [
                PermissionType::VIEW_ANESTHESIA_SHEET->value,
                PermissionType::VIEW_BATCH->value,
                // ... según necesites
            ],
            
            RoleType::AUXILIARBDG->value => [
                PermissionType::VIEW_BATCH->value,
                PermissionType::EDIT_BATCH->value,
                // ... según necesites
            ],
        ];
        // Creación de permisos a partir de los casos del Enum PermissionType
        /* foreach (PermissionType::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
                'team_id' => 2, // Puedes asignar un team_id si es multitenant
                // Puedes incluir otros campos o asignar un team_id si es multitenant
            ]);
        }

        // Creación de roles a partir de los casos del Enum RoleType
        foreach (RoleType::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
                'team_id' => 2, // Puedes asignar un team_id si es multitenant
                // Puedes incluir otros campos o asignar un team_id si es multitenant
            ]);
        } */

        /* $user = User::find(1);
        $role = Role::firstOrCreate([
            'name' => 'Super-Admin',
            'guard_name' => 'web',
            'team_id' => 1
        ]);
        // Establece el team_id antes de asignar el rol
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(1);
        $user->assignRole($role); */
    }
}
