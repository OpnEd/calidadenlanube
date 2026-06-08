<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Team;
use App\Models\Document;
use App\Models\Process;
use App\Models\ProcessType;
use App\Models\Quality\DocumentVersion;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function documentDetails(Request $request, \App\Models\Team $tenant, Document $document)
    {
        //dd($tenant, $document);

        if ((int) $document->team_id !== (int) $tenant->id) {
            abort(404);
        }

        // (1) Asegurar contexto tenant
        Filament::setTenant($tenant);
        $tenantId = $tenant->id;

        // (2) Fijar también el contexto para Spatie Permissions
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($tenantId);

        $user = Auth::user();
        $canSeeUnapproved = $user?->can('edit-document') ?? false;

        if (! $document->isApproved() && ! $canSeeUnapproved) {
            abort(403, 'Documento no aprobado para uso operativo.');
        }

        $versions = DocumentVersion::with('user')             // para acceder a quien hizo el cambio
            ->where('document_id', $document->id)             // filtra solo las de este documento
            ->orderBy('created_at', 'desc')                    // orden decreciente
            ->get();

        $preparer = $document->preparedBy;
        $reviewer = $document->reviewedBy;
        $approver = $document->approvedBy;

        // 2) Si no hay reviewer, defines un texto y saltas la parte de roles
        if (! $preparer) {
            $preparerName      = '-';
            $preparerRoleName = '-'; // o ['—']
            $preparerSignature = '-'; // o ''
        } else {
            // 3) Si sí existe, ya trabajas con el modelo
            $preparerName = $preparer->name;
            $preparerSignature = $preparer->signature;

            // 4) Obtienes sus roles en el contexto del tenant
            $preparerRole = $preparer->roles()
                ->where('model_has_roles.team_id', $tenantId)
                ->where(function ($query) use ($tenantId) {
                    $query->whereNull('roles.team_id')
                        ->orWhere('roles.team_id', $tenantId);
                })
                ->first();

            $preparerRoleName = $preparerRole?->name ?? 'Consultor';
        }

        // 2) Si no hay reviewer, defines un texto y saltas la parte de roles
        if (! $reviewer) {
            $reviewerName      = 'Sin revisar';
            $reviewerRoleName = '-'; // o ['—']
            $reviewerSignature = '-'; // o ''
        } else {
            // 3) Si sí existe, ya trabajas con el modelo
            $reviewerName = $reviewer->name;
            $reviewerSignature = $reviewer->signature;

            // 4) Obtienes sus roles en el contexto del tenant
            $reviewerRole = $reviewer->roles()
                ->where('model_has_roles.team_id', $tenantId)
                ->where(function ($query) use ($tenantId) {
                    $query->whereNull('roles.team_id')
                        ->orWhere('roles.team_id', $tenantId);
                })
                ->first();

            $reviewerRoleName = $reviewerRole?->name ?? '-';
        }

        // 2) Si no hay reviewer, defines un texto y saltas la parte de roles
        if (! $approver) {
            $approverName      = 'Sin aprobar';
            $approverRoleName = '-'; // o ['—']
            $approverSignature = '-'; // o ''
        } else {
            // 3) Si sí existe, ya trabajas con el modelo
            $approverName = $approver->name;
            $approverSignature = $approver->signature;

            // 4) Obtienes sus roles en el contexto del tenant
            $approverRole = $approver->roles()
                ->where('model_has_roles.team_id', $tenantId)
                ->where(function ($query) use ($tenantId) {
                    $query->whereNull('roles.team_id')
                        ->orWhere('roles.team_id', $tenantId);
                })
                ->first();

            $approverRoleName = $approverRole?->name ?? '-';
        }

        // (4) Entregar al usuario
        //return $pdf->stream("{$document->slug}.pdf");
        //$document = Document::where('id', $document)->first();
        $process = Process::where('id', $document->process_id)->first();
        $processType = ProcessType::where('id', $process->process_type_id)->first();
        $documentStrReplace = str_replace(' ', '-', $document->title);
        $documentCategory = $document->document_category->name;
        //dd($document->data['validity']);
        $documentCode = $document->process->code . '-' . $document->document_category->code . '-' . $document->sequence_padded;
        //$company = Team::first();
        $label = "";
        switch ($documentCategory) {
            case "Manual":
                $label = "Manual";
                break;
            case " Caracterización de procesos":
                $label = " Caracterización de procesos";
                break;
            case "Indicador de gestión":
                $label = "Indicador de gestión";
                break;
            case "Procedimiento":
                $label = "Procedimiento";
                break;
            case "Instrucción":
                $label = "Instrucción";
                break;
            case "Formulario":
                $label = "Formulario";
                break;
            case "Tabla o Matriz":
                $label = "Tabla o Matriz";
                break;
            case "Gráficos e ilustraciones":
                $label = "Gráficos e ilustraciones";
                break;
        }

        $pdf = Pdf::loadView('drugdocs.documentDetails', [
            'document' => $document,
            'documentCode' => $documentCode,
            'processType' => $processType,
            'process' => $process,
            'label' => $label,
            'company' => $tenant,
            'preparer' => $preparerName,
            'preparerRole' => $preparerRoleName,
            'preparerSignature' => $preparerSignature,
            'reviewer' => $reviewerName,
            'reviewerRole' => $reviewerRoleName,
            'reviewerSignature' => $reviewerSignature,
            'approver' => $approverName,
            'approverRole' => $approverRoleName,
            'approverSignature' => $approverSignature,
            'versions' => $versions,
        ]);
        //$pdf->setEncryption('password','drogueria',['print']);
        return $pdf->stream($documentStrReplace . '.pdf');
    }
}
