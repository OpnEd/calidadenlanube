<?php

namespace App\Http\Controllers;

use Database\Seeders\RoleSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RoleSeederController extends Controller
{
    /**
     * Ejecuta el RoleSeeder para un team específico
     */
    public function seed(Request $request, int $teamId)
    {
        // Validar que el usuario esté autenticado y sea admin
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }
        
        try {
            // Ejecutar el seeder
            $seeder = new RoleSeeder();
            $seeder->setCommand($this->artisanCommand());
            $seeder->run($teamId);
            
            return response()->json([
                'status' => 'success',
                'message' => "Roles y permisos creados exitosamente para team_id: {$teamId}",
                'team_id' => $teamId,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error en RoleSeeder: {$e->getMessage()}");
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear roles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene el comando Artisan para logging
     */
    private function artisanCommand()
    {
        return new class {
            public function info($message) {
                Log::info($message);
            }
            public function error($message) {
                Log::error($message);
            }
        };
    }
}
