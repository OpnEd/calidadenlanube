<?php

namespace App\Http\Controllers\Quality\Training;

use App\Models\Quality\Training\Certificate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateDownloadController
{
    /**
     * Descargar PDF del certificado
     */
    public function download(Certificate $certificate): StreamedResponse
    {
        // Validar autorización
        if ($certificate->user_id !== auth()->id() && !auth()->user()->can('download', $certificate)) {
            abort(403, 'No autorizado para descargar este certificado.');
        }

        // Validar que el certificado existe y tiene PDF
        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'El archivo del certificado no se encuentra disponible.');
        }

        // Obtener el archivo
        $path = Storage::disk('public')->path($certificate->pdf_path);

        // Descargar archivo
        return response()->download(
            $path,
            $certificate->pdf_filename ?? "certificado_{$certificate->certificate_number}.pdf",
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }

    /**
     * Ver preview del certificado en navegador
     */
    public function preview(Certificate $certificate): StreamedResponse
    {
        // Validar autorización
        if ($certificate->user_id !== auth()->id() && !auth()->user()->can('view', $certificate)) {
            abort(403, 'No autorizado para ver este certificado.');
        }

        // Validar que exista el PDF
        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'El archivo del certificado no se encuentra disponible.');
        }

        $path = Storage::disk('public')->path($certificate->pdf_path);

        return response()->file(
            $path,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($certificate->pdf_filename ?? "certificado.pdf") . '"',
            ]
        );
    }

    /**
     * Obtener URL de descarga del certificado
     */
    public function getDownloadUrl(Certificate $certificate): \Illuminate\Http\Response
    {
        // Validar autorización
        if ($certificate->user_id !== auth()->id() && !auth()->user()->can('view', $certificate)) {
            abort(403, 'No autorizado.');
        }

        if (!$certificate->pdf_path) {
            return response()->json(['error' => 'PDF no disponible'], 404);
        }

        return response()->json([
            'url' => Storage::disk('public')->url($certificate->pdf_path),
            'filename' => $certificate->pdf_filename,
        ]);
    }
}
