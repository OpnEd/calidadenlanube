<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\WasteGenerationReport;
use App\Services\Quality\WasteGenerationReportService;
use App\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WasteGenerationReportController extends Controller
{
    /**
     * Descargar PDF del informe de residuos
     * Route: admin/{tenant}/informes/residuos/{report:numero_informe}.pdf
     */
    public function downloadLastYear(Team $tenant, WasteGenerationReport $report)
    {
        // DEBUG: Descomenta la siguiente línea para ver en pantalla los datos recibidos
        // dd('Controlador alcanzado', ['tenant' => $tenant, 'report' => $report]);

        $requestId = uniqid('req_');
        $startTime = microtime(true);

        try {
            Log::info("Iniciando descarga de PDF", [
                'request_id' => $requestId,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'report_id' => $report->id,
                'report_numero' => $report->numero_informe,
                'user_id' => Auth::user()->id,
            ]);

            // Asegurar contexto tenant
            Filament::setTenant($tenant);
            $tenantId = $tenant->id;

            Log::debug("Contexto Filament configurado", [
                'request_id' => $requestId,
                'tenant_id' => $tenantId,
            ]);

            // Fijar contexto para Spatie Permissions
            app(\Spatie\Permission\PermissionRegistrar::class)
                ->setPermissionsTeamId($tenantId);

            Log::debug("Permisos Spatie configurados", [
                'request_id' => $requestId,
                'tenant_id' => $tenantId,
            ]);

            // Validar que el informe pertenece al tenant
            if ($report->team_id !== $tenantId) {
                Log::warning("Acceso denegado: informe no pertenece al tenant", [
                    'request_id' => $requestId,
                    'report_id' => $report->id,
                    'report_team_id' => $report->team_id,
                    'requested_tenant_id' => $tenantId,
                    'user_id' => Auth::user()->id,
                ]);
                abort(403, 'No autorizado para descargar este informe.');
            }

            Log::info("Validación de acceso completada", [
                'request_id' => $requestId,
                'report_id' => $report->id,
                'tenant_id' => $tenantId,
            ]);

            // Usar el servicio para generar y descargar PDF
            Log::info("Iniciando generación de PDF", [
                'request_id' => $requestId,
                'report_numero' => $report->numero_informe,
                'report_anio' => $report->anio,
                'report_estado' => $report->estado,
            ]);

            $pdf = WasteGenerationReportService::descargarPDF($report);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("Descarga de PDF completada exitosamente", [
                'request_id' => $requestId,
                'report_numero' => $report->numero_informe,
                'duration_ms' => $duration,
                'user_id' => Auth::user()->id,
                'tenant_id' => $tenantId,
            ]);

            return $pdf;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Informe no encontrado", [
                'request_id' => $requestId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'tenant_id' => $tenant->id,
                'user_id' => Auth::user()->id,
            ]);
            abort(404, 'El informe solicitado no fue encontrado.');
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::error("Error al descargar PDF", [
                'request_id' => $requestId,
                'report_numero' => $report->numero_informe ?? 'unknown',
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'duration_ms' => $duration,
                'tenant_id' => $tenant->id,
                'user_id' => Auth::user()->id,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);
            abort(500, 'Error al generar el PDF. Por favor intenta más tarde.');
        }
    }
}
