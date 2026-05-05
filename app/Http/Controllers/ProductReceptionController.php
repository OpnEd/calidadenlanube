<?php

namespace App\Http\Controllers;

use App\Models\ProductReception;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class ProductReceptionController extends Controller
{
    public function downloadPdf(Request $request, Team $tenant, ProductReception $productReception)
    {
        Filament::setTenant($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        $productReception->load([
            'team',
            'user',
            'purchase.supplier',
            'invoice',
            'items.product',
            'items.batch',
        ]);

        if ((int) $productReception->team_id !== (int) $tenant->id) {
            abort(404);
        }

        if (auth()->check() && auth()->user()->cannot('view', $productReception)) {
            abort(403);
        }

        $items = $productReception->items;
        $totalQuantity = (float) $items->sum('quantity');
        $totalAmount = (float) $items->sum('total');
        $purchaseCode = $productReception->purchase?->code ?? ('PR-' . $productReception->id);
        $fileName = Str::slug('product-reception-' . $purchaseCode) . '.pdf';

        $pdf = Pdf::loadView('product-receptions.pdf', [
            'productReception' => $productReception,
            'tenant' => $tenant,
            'items' => $items,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'printedAt' => now(),
            'downloadedBy' => auth()->user()?->name ?? 'Sistema',
        ])
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream($fileName);
    }
}

