<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $documentCode ?? $document->code }} - {{ $document->title }}</title>
    <style>
        @page { margin: 165px 35px 88px 35px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.4; }
        .page-header { position: fixed; top: -145px; left: 0; right: 0; height: 125px; }
        .page-footer { position: fixed; bottom: -74px; left: 0; right: 0; height: 62px; color: #374151; font-size: 10px; }
        .iso-frame,
        .iso-footer,
        .iso-control-table { width: 100%; border-collapse: collapse; margin: 0; }
        .iso-frame td,
        .iso-footer td,
        .iso-control-table th,
        .iso-control-table td { border: 1px solid #9ca3af; padding: 4px 6px; vertical-align: middle; }
        .iso-company { width: 30%; font-size: 10px; }
        .iso-company-logo { display: block; max-height: 40px; max-width: 130px; margin-bottom: 4px; }
        .iso-doc-title { width: 42%; text-align: center; font-size: 11px; font-weight: bold; }
        .iso-doc-title span { font-weight: normal; font-size: 10px; }
        .iso-control { width: 28%; padding: 0; }
        .iso-control-table th { width: 42%; font-weight: bold; background: #f3f4f6; text-align: left; }
        .iso-footer td { font-size: 9px; }
        .page-number { text-align: right; }
        .content-wrap { margin-top: 0; }
        .header { border: 1px solid #d1d5db; padding: 10px; margin-bottom: 14px; }
        .header-top { margin-bottom: 8px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .muted { color: #6b7280; }
        .meta { margin-top: 8px; line-height: 1.6; }
        .section { margin-top: 14px; }
        .section h2 { font-size: 13px; margin: 0 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; }
        ul { margin: 0; padding-left: 18px; }
        li { margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; text-align: left; }
        th { background-color: #f3f4f6; }
        .small { font-size: 11px; }
        .signature-table td { width: 33.33%; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
    </style>
</head>
<body>
    <div class="page-header small">
        <table class="iso-frame">
            <tr>
                <td class="iso-company">
                    @if (!empty($companyLogoPath))
                        <img src="{{ $companyLogoPath }}" alt="Logo" class="iso-company-logo">
                    @endif
                    <strong>{{ $company->name ?? 'Organizacion' }}</strong><br>
                    Sistema de Gestion de Calidad
                </td>
                <td class="iso-doc-title">
                    {{ $document->title }}<br>
                    <span>{{ $label ?? ($document->documentCategory?->name ?? 'Documento') }}</span>
                </td>
                <td class="iso-control">
                    <table class="iso-control-table">
                        <tr>
                            <th>Codigo</th>
                            <td class="mono">{{ $documentCode ?? $document->code ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Version</th>
                            <td>{{ $document->currentVersion?->version ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>{{ $document->status ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        <table class="iso-footer">
            <tr>
                <td><strong>Codigo de copia:</strong> {{ $copyCode ?? 'N/A' }}</td>
                <td><strong>Fecha de impresion:</strong> {{ ($printedAt ?? now())->format('d/m/Y H:i') }}</td>
                <td><strong>Descargado por:</strong> {{ $downloadedBy ?? 'Sistema' }}</td>
            </tr>
            <tr>
                <td colspan="2">Documento vigente en sistema. Copia no controlada si se imprime.</td>
                <td class="page-number"></td>
            </tr>
        </table>
    </div>

    <div class="content-wrap">
    <div class="header">
        <div class="header-top">
            <p class="title">{{ $document->title }}</p>
            <div class="small muted">{{ $company->name ?? 'Organizacion' }}</div>
        </div>
        <table class="small">
            <tbody>
                <tr>
                    <th style="width: 22%">Codigo</th>
                    <td class="mono" style="width: 28%">{{ $documentCode ?? $document->code ?? 'N/A' }}</td>
                    <th style="width: 22%">Version</th>
                    <td style="width: 28%">{{ $document->currentVersion?->version ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Categoria</th>
                    <td>{{ $label ?? ($document->documentCategory?->name ?? 'N/A') }}</td>
                    <th>Estado</th>
                    <td>{{ $document->status ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Tipo de proceso</th>
                    <td>{{ $processType?->name ?? 'N/A' }}</td>
                    <th>Proceso</th>
                    <td>{{ $process?->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Vigencia</th>
                    <td>{{ $document->effective_at?->format('d/m/Y') ?? 'N/A' }}</td>
                    <th>Expiracion</th>
                    <td>{{ $document->expires_at?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Control de aprobacion</h2>
        <table class="small signature-table">
            <thead>
                <tr>
                    <th>Elaboro</th>
                    <th>Reviso</th>
                    <th>Aprobo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>{{ $preparer ?? '-' }}</strong><br>{{ $preparerRole ?? '-' }}<br>Firma: {{ $preparerSignature ?? '-' }}</td>
                    <td><strong>{{ $reviewer ?? 'Sin revisar' }}</strong><br>{{ $reviewerRole ?? '-' }}<br>Firma: {{ $reviewerSignature ?? '-' }}</td>
                    <td><strong>{{ $approver ?? 'Sin aprobar' }}</strong><br>{{ $approverRole ?? '-' }}<br>Firma: {{ $approverSignature ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Control de cambios</h2>
        @if (!empty($versions) && $versions->count())
            <table class="small">
                <thead>
                    <tr>
                        <th style="width: 10%">Version</th>
                        <th style="width: 14%">Estado</th>
                        <th style="width: 28%">Cambio</th>
                        <th style="width: 18%">Creado por</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 15%">Publicacion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($versions as $version)
                        <tr>
                            <td>{{ $version->version }}</td>
                            <td>{{ $version->status }}</td>
                            <td>{{ $version->change_summary ?: '-' }}</td>
                            <td>{{ $version->creator?->name ?? '-' }}</td>
                            <td>{{ $version->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $version->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin versiones registradas.</p>
        @endif
    </div>

    <div class="section">
        <h2>Alcance y objetivo</h2>
        <div class="meta small">
            <strong>Documento:</strong> {{ $label ?? 'Documento' }}<br>
            <strong>Descripcion:</strong> {{ $document->description ?? 'N/A' }}
        </div>
    </div>

    <div class="section">
        <h2>Objetivos</h2>
        @if (!empty($content['objectives']))
            <ul>
                @foreach ($content['objectives'] as $objective)
                    <li>{{ $objective }}</li>
                @endforeach
            </ul>
        @else
            <p class="muted">Sin objetivos registrados.</p>
        @endif
    </div>

    <div class="section">
        <h2>Condiciones Generales</h2>
        @if (!empty($content['general_conditions']))
            {!! $content['general_conditions'] !!}
        @else
            <p class="muted">Sin condiciones generales registradas.</p>
        @endif
    </div>

    <div class="section">
        <h2>Definiciones</h2>
        @if (!empty($content['definitions']))
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Termino</th>
                        <th>Definicion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($content['definitions'] as $term => $definition)
                        <tr>
                            <td>{{ $term }}</td>
                            <td>{{ $definition }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin definiciones registradas.</p>
        @endif
    </div>

    <div class="section">
        <h2>Referencias Normativas</h2>
        @if (!empty($content['normative_references']))
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Norma</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($content['normative_references'] as $standard => $reference)
                        <tr>
                            <td>{{ $standard }}</td>
                            <td>{{ $reference }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin referencias normativas registradas.</p>
        @endif
    </div>

    <div class="section">
        <h2>Procedimiento</h2>
        @if (!empty($content['procedure']))
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%">Actividad</th>
                        <th style="width: 42%">Descripcion de actividad</th>
                        <th style="width: 20%">Responsable</th>
                        <th style="width: 20%">Registros</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($content['procedure'] as $row)
                        <tr>
                            <td>{{ $row['activity'] ?? '' }}</td>
                            <td>{{ $row['activity_description'] ?? '' }}</td>
                            <td>{{ $row['responsible'] ?? '' }}</td>
                            <td>{{ $row['records'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin procedimiento registrado.</p>
        @endif
    </div>
    </div>
</body>
</html>
