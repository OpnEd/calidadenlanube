<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Product Reception</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 24px;
        }

        .header {
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
            color: #0f766e;
            letter-spacing: 0.5px;
        }

        .muted {
            color: #6b7280;
            font-size: 11px;
        }

        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 12px;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            vertical-align: top;
        }

        .card-title {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }

        .card-value {
            margin: 0;
            font-size: 13px;
            color: #111827;
            font-weight: 600;
        }

        .status {
            display: inline-block;
            font-size: 10px;
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .status-done {
            background: #059669;
        }

        .status-progress {
            background: #d97706;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        table.items thead th {
            background: #f3f4f6;
            color: #374151;
            font-size: 11px;
            text-align: left;
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        table.items td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-top: 12px;
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
        }

        .totals .label {
            background: #f9fafb;
            width: 75%;
            font-weight: 600;
        }

        .totals .value {
            text-align: right;
            width: 25%;
            font-weight: 700;
        }

        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Product Reception</h1>
    <div class="muted">
        Team: {{ $tenant->name ?? 'N/A' }} |
        Fecha de impresion: {{ $printedAt->format('d/m/Y H:i') }} |
        Generado por: {{ $downloadedBy }}
    </div>
</div>

<table class="grid">
    <tr>
        <td class="card">
            <p class="card-title">Purchase</p>
            <p class="card-value">{{ $productReception->purchase?->code ?? 'N/A' }}</p>
        </td>
        <td class="card">
            <p class="card-title">Invoice</p>
            <p class="card-value">{{ $productReception->invoice?->code ?? 'N/A' }}</p>
        </td>
        <td class="card">
            <p class="card-title">Estado</p>
            <p class="card-value">
                @php
                    $status = (string) $productReception->status;
                    $isDone = $status === \App\Models\ProductReception::STATUS_DONE;
                @endphp
                <span class="status {{ $isDone ? 'status-done' : 'status-progress' }}">
                    {{ strtoupper($status) }}
                </span>
            </p>
        </td>
    </tr>
    <tr>
        <td class="card">
            <p class="card-title">Fecha recepcion</p>
            <p class="card-value">{{ optional($productReception->reception_date)->format('d/m/Y H:i') ?? 'N/A' }}</p>
        </td>
        <td class="card">
            <p class="card-title">Recibido por</p>
            <p class="card-value">{{ $productReception->user?->name ?? 'N/A' }}</p>
        </td>
        <td class="card">
            <p class="card-title">Proveedor</p>
            <p class="card-value">{{ $productReception->purchase?->supplier?->name ?? 'N/A' }}</p>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
    <tr>
        <th>Producto</th>
        <th>Lote</th>
        <th class="text-right">Cantidad</th>
        <th class="text-right">Precio compra</th>
        <th class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    @forelse($items as $item)
        <tr>
            <td>{{ $item->product?->name ?? 'N/A' }}</td>
            <td>{{ $item->batch?->code ?? 'Sin lote' }}</td>
            <td class="text-right">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
            <td class="text-right">${{ number_format((float) $item->purchase_price, 2, ',', '.') }}</td>
            <td class="text-right">${{ number_format((float) $item->total, 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5">No hay items registrados para esta recepcion.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<table class="totals">
    <tr>
        <td class="label">Cantidad total</td>
        <td class="value">{{ number_format((float) $totalQuantity, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label">Valor total</td>
        <td class="value">${{ number_format((float) $totalAmount, 2, ',', '.') }}</td>
    </tr>
</table>

@if(!empty($productReception->observations))
    <div style="margin-top: 14px;">
        <strong>Observaciones:</strong>
        <div>{{ $productReception->observations }}</div>
    </div>
@endif

<div class="footer">
    Codigo interno: PR-{{ $productReception->id }}
</div>
</body>
</html>

