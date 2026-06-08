<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\Records\Improvement\ImprovementPlan;
use App\Models\Team;
use App\Enums\ImprovementPlanStatus;
use App\Enums\TaskStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImprovementPlanController extends Controller
{
    /**
     * Descargar PDF del plan de mejora.
     * Route: admin/{tenant}/planes-mejora/{plan}.pdf
     */
    public function download(Team $tenant, ImprovementPlan $plan)
    {
        $requestId = uniqid('plan_');

        try {
            Filament::setTenant($tenant);
            $tenantId = $tenant->id;

            app(\Spatie\Permission\PermissionRegistrar::class)
                ->setPermissionsTeamId($tenantId);

            if ($plan->team_id !== $tenantId) {
                abort(403, 'No autorizado para descargar este plan.');
            }

            $plan->load([
                'team',
                'checklistItemAnswer.checklistItem.checklist.process',
                'checklistItemAnswer.user',
                'tasks.user',
                'tasks.checklistItem',
            ]);

            $pdf = Pdf::loadView('informes.plan-mejora-pdf', [
                'plan' => $plan,
                'statusLabel' => $plan->status instanceof ImprovementPlanStatus
                    ? $plan->status->label()
                    : ImprovementPlanStatus::tryFrom((string) $plan->status)?->label(),
                'generated_at' => now()->format('d/m/Y H:i'),
                'generated_by' => Auth::user()?->name ?? 'Sistema',
                'taskStatusLabels' => collect(TaskStatus::cases())
                    ->mapWithKeys(fn (TaskStatus $status) => [$status->value => $status->label()])
                    ->all(),
            ])->setPaper('a4');

            $fileName = 'plan-mejora-' . $plan->id . '.pdf';

            return $pdf
                ->download($fileName)
                ->header('Content-Type', 'application/pdf; charset=utf-8')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        } catch (\Throwable $e) {
            Log::error('Error al generar PDF del plan de mejora', [
                'request_id' => $requestId,
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            abort(500, 'Error al generar el PDF. Por favor intenta mas tarde.');
        }
    }
}
