<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class DisablePermissionTeams
{
    public function handle(Request $request, Closure $next): Response
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamsState = $permissionRegistrar->teams;

        $permissionRegistrar->teams = false;

        try {
            return $next($request);
        } finally {
            $permissionRegistrar->teams = $originalTeamsState;
        }
    }
}

/**
 * La clase DisablePermissionTeams es un middleware diseñado para desactivar temporalmente la funcionalidad de "equipos" (teams) del paquete spatie/laravel-permission durante el ciclo de vida de una solicitud específica.

* Aquí te explico detalladamente qué hace y por qué es útil en tu proyecto:

* ¿Qué hace exactamente el código?
* Captura el estado actual: Accede al PermissionRegistrar (el núcleo del paquete de permisos) y guarda en una variable ($originalTeamsState) si la función de equipos está encendida o apagada en ese momento.
* Desactiva el scoping de equipos: Fuerza la propiedad $permissionRegistrar->teams a false. Al hacer esto, el paquete deja de filtrar roles y permisos por la columna team_id. En este estado, las comprobaciones de permisos se vuelven "globales".
* Ejecuta la petición: Permite que la solicitud continúe su curso hacia el siguiente middleware o controlador mediante $next($request).
* Restaura el estado original: Utiliza un bloque finally para asegurar que, sin importar si la petición terminó bien o hubo un error, la configuración de $teams vuelva a su estado original. Esto es crucial para no afectar a otras partes de la aplicación o a futuras peticiones en entornos donde el estado persiste.
* ¿Para qué sirve en el contexto de tu aplicación?
* En tu sistema (que es multi-tenant), normalmente usas el middleware SetTeamPermissions para que cada usuario vea solo los roles de su clínica o empresa. Sin embargo, existen casos especiales:
* 
* Super Administradores Globales: Si tienes un panel de administración central (como el tenantManager que mencionas en User.php), necesitas verificar roles que no están atados a un team_id específico (roles globales).
* Evitar conflictos de Scope: Cuando teams está activo, Spatie siempre intenta añadir un where team_id = ... a las consultas. Si intentas validar un permiso global mientras el scope de equipo está activo, la consulta fallará o no encontrará el rol. Este middleware "limpia" esa restricción para esa ruta en particular.
* Ejemplo de uso
* Probablemente lo estés aplicando en las rutas del panel tenantManager o en procesos de sistema donde necesitas que un usuario actúe por fuera de los límites de un "equipo" específico.
 */
