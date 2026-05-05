<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Quality\Documentation\DocumentVersion;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;

class DocumentController extends Controller
{
    public function downloadPdf(Request $request, Team $tenant, Document $document)
    {
        Filament::setTenant($tenant);
        $tenantId = $tenant->id;

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $document->load([
            'team',
            'owner',
            'process.process_type',
            'documentCategory',
            'currentVersion',
            'reviews.reviewer',
            'approvals.approver',
            'versions.creator',
        ]);

        if ((int) $document->team_id !== (int) $tenantId) {
            abort(404);
        }

        if (auth()->check() && auth()->user()->cannot('view', $document)) {
            abort(403);
        }

        $versions = DocumentVersion::with('creator')
            ->where('document_id', $document->id)
            ->orderByDesc('created_at')
            ->get();

        $preparerInfo = $this->buildUserInfo($document->owner, $tenantId, '-');

        $latestReview = $document->reviews
            ->sortByDesc('reviewed_at')
            ->first();
        $reviewerInfo = $this->buildUserInfo($latestReview?->reviewer, $tenantId, 'Sin revisar');

        $latestApproval = $document->approvals
            ->sortByDesc('approved_at')
            ->first();
        $approverInfo = $this->buildUserInfo($latestApproval?->approver, $tenantId, 'Sin aprobar');

        $process = $document->process;
        $processType = $process?->process_type;
        $documentCategory = $document->documentCategory?->name;
        $label = $this->resolveDocumentLabel($documentCategory);

        $content = $document->structured_content;
        $documentFilename = Str::slug($document->title ?: ($document->code ?: 'documento')) . '.pdf';
        $companyLogoPath = $this->resolveTenantLogoPath($tenant);
        $printedAt = now();
        $downloadedBy = auth()->user()?->name ?? 'Sistema';
        $copyCode = sprintf(
            'COP-%s-%s-%s',
            (string) $tenantId,
            (string) $document->id,
            $printedAt->format('YmdHis')
        );

        $pdf = Pdf::loadView('documents.pdf', [
            'document' => $document,
            'content' => $content,
            'documentCode' => $document->code,
            'processType' => $processType,
            'process' => $process,
            'label' => $label,
            'company' => $tenant,
            'companyLogoPath' => $companyLogoPath,
            'preparer' => $preparerInfo['name'],
            'preparerRole' => $preparerInfo['role'],
            'preparerSignature' => $preparerInfo['signature'],
            'reviewer' => $reviewerInfo['name'],
            'reviewerRole' => $reviewerInfo['role'],
            'reviewerSignature' => $reviewerInfo['signature'],
            'approver' => $approverInfo['name'],
            'approverRole' => $approverInfo['role'],
            'approverSignature' => $approverInfo['signature'],
            'versions' => $versions,
            'printedAt' => $printedAt,
            'downloadedBy' => $downloadedBy,
            'copyCode' => $copyCode,
        ])
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $pdf->render();

        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(
            $canvas->get_width() - 130,
            $canvas->get_height() - 26,
            'Pagina {PAGE_NUM} de {PAGE_COUNT}',
            $font,
            9,
            [0.22, 0.25, 0.29]
        );

        return $pdf->stream($documentFilename);
    }

    private function buildUserInfo($user, int $tenantId, string $defaultName): array
    {
        if (!$user) {
            return [
                'name' => $defaultName,
                'role' => '-',
                'signature' => '-',
            ];
        }

        $role = $user->roles()
            ->where('model_has_roles.team_id', $tenantId)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('roles.team_id')
                    ->orWhere('roles.team_id', $tenantId);
            })
            ->first();

        return [
            'name' => $user->name ?? $defaultName,
            'role' => $role?->name ?? '-',
            'signature' => data_get($user, 'signature', data_get($user, 'data.signature', '-')) ?: '-',
        ];
    }

    private function resolveDocumentLabel(?string $documentCategory): string
    {
        return match (trim((string) $documentCategory)) {
            'Manual' => 'Manual',
            'Caracterizacion de procesos', 'Caracterización de procesos' => 'Caracterizacion de procesos',
            'Indicador de gestion', 'Indicador de gestión' => 'Indicador de gestion',
            'Procedimiento' => 'Procedimiento',
            'Instruccion', 'Instrucción' => 'Instruccion',
            'Formulario' => 'Formulario',
            'Tabla o Matriz' => 'Tabla o Matriz',
            'Graficos e ilustraciones', 'Gráficos e ilustraciones' => 'Graficos e ilustraciones',
            default => $documentCategory ?: 'Documento',
        };
    }

    private function resolveTenantLogoPath(Team $tenant): ?string
    {
        $candidates = [
            data_get($tenant, 'logo_path'),
            data_get($tenant, 'logo'),
            data_get($tenant, 'data.logo_path'),
            data_get($tenant, 'data.logo'),
            data_get($tenant, 'data.logo_url'),
            data_get($tenant, 'data.logo_file'),
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $candidate = trim($candidate);

            if (Str::startsWith($candidate, ['http://', 'https://'])) {
                return $candidate;
            }

            $normalized = ltrim(str_replace('\\', '/', $candidate), '/');
            $localPaths = [
                public_path($normalized),
                public_path('storage/' . $normalized),
                storage_path('app/public/' . $normalized),
            ];

            foreach ($localPaths as $path) {
                if (File::exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }
}
