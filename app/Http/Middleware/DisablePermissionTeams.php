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
