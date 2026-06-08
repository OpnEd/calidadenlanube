<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Process;
use App\Models\ProcessType;
use App\Models\Role;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class ProcessCaracterizationController extends Controller
{
    public function generateCharacterization(Team $tenant, Process $process)
{
    // Verificar que el proceso pertenece al tenant
    if ($process->team_id !== $tenant->id) {
        abort(403, 'Este proceso no pertenece a este tenant.');
    }

    // Tipo de proceso
    $processType = ProcessType::find($process->process_type_id);

    // Responsable del proceso
    $processResponsible = $process->role_id
        ? Role::find($process->role_id)?->name
        : 'Por definir';

    // Cargar metas de calidad con indicadores filtrados por team
    $qualityGoals = $process->qualityGoals()
        ->with(['managementIndicators' => function ($q) use ($tenant) {
            $q->whereHas('teams', function ($q2) use ($tenant) {
                $q2->where('team_id', $tenant->id);
            });
        }])
        ->where('team_id', $tenant->id)
        ->get();

    // Aplanar los indicadores de gestión para pasarlos a la vista
    $managementIndicators = $qualityGoals
        ->flatMap(function ($goal) {
            return $goal->managementIndicators->map(function ($indicator) use ($goal) {
                return [
                    'goal_name'   => $goal->name,
                    'name'        => $indicator->name,
                    'objective'   => $indicator->objective,
                    'type'        => $indicator->type,
                    'description' => $indicator->description,
                    'information_source' => $indicator->information_source,
                    'numerator'   => $indicator->numerator,
                    'denominator' => $indicator->denominator,
                ];
            });
        })
        ->values();

    $documentName = 'Caracterizacion_' . $process->slug . '_' . $tenant->slug;
    $documentType = 'Caracterización de proceso';

    $pdf = Pdf::loadView('drugdocs.process-characterization', [
        'process'             => $process,
        'tenant'              => $tenant,
        'processType'         => $processType,
        'processTypeCode'     => $processType->code ?? 'N/A',
        'processResponsible'  => $processResponsible,
        'documentType'        => $documentType,
        'managementIndicators'=> $managementIndicators,
    ]);

    $pdf->setPaper('A4', 'landscape');

    return $pdf->stream($documentName . '.pdf');
}

}
