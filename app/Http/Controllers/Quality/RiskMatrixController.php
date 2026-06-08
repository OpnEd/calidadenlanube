<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\Quality\RiskAssessment\Risk;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RiskMatrixController extends Controller
{
    /**
     * Descargar PDF de la matriz de riesgos.
     * Route: admin/{tenant}/matriz-riesgos.pdf
     */
    public function download(Team $tenant)
    {
        $requestId = uniqid('risk_');

        try {
            Filament::setTenant($tenant);
            $tenantId = $tenant->id;

            app(\Spatie\Permission\PermissionRegistrar::class)
                ->setPermissionsTeamId($tenantId);

            $processId = request()->integer('process_id');

            $risks = Risk::query()
                ->with(['process', 'owner'])
                ->where('team_id', $tenantId)
                ->when($processId, fn ($query) => $query->where('process_id', $processId))
                ->orderBy('process_id')
                ->orderByDesc('risk_score')
                ->get();

            $process = $processId
                ? Process::query()
                    ->where('id', $processId)
                    ->where('team_id', $tenantId)
                    ->first()
                : null;

            $pdf = Pdf::loadView('informes.matriz-riesgos-pdf', [
                'team' => $tenant,
                'process' => $process,
                'risks' => $risks,
                'generated_at' => now()->format('d/m/Y H:i'),
                'generated_by' => Auth::user()?->name ?? 'Sistema',
            ])->setPaper('a4', 'landscape');

            $fileName = 'matriz-riesgos' . ($process ? '-' . $process->name : '') . '.pdf';

            return $pdf
                ->download($fileName)
                ->header('Content-Type', 'application/pdf; charset=utf-8')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        } catch (\Throwable $e) {
            Log::error('Error al generar PDF de matriz de riesgos', [
                'request_id' => $requestId,
                'tenant_id' => $tenant->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            abort(500, 'Error al generar el PDF. Por favor intenta mas tarde.');
        }
    }
}
