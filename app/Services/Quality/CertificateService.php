<?php

namespace App\Services\Quality;

use App\Models\Quality\Training\Certificate;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CertificateService
{
    /**
     * Generar un certificado para una inscripción completada
     */
    public function generateCertificate(
        Enrollment $enrollment,
        User $user,
        Course $course,
        ?float $finalScore = null,
        ?string $templateName = 'default'
    ): Certificate {
        try {
            // Validar que la inscripción está completada
            if (!$enrollment->isCompleted()) {
                throw new \RuntimeException('La inscripción debe estar completada para generar un certificado.');
            }

            // Crear o actualizar el registro de certificado
            $certificate = Certificate::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $course->id,
                ],
                [
                    'user_id' => $user->id,
                    'team_id' => $enrollment->team_id,
                    'certificate_number' => Certificate::generateCertificateNumber(),
                    'title' => $course->title,
                    'description' => $course->description ?? "Certificado de Finalización",
                    'issuer' => config('app.name', 'D-Origin 2.0'),
                    'final_score' => $finalScore,
                    'status' => 'pending',
                    'template_used' => $templateName ?? 'default',
                ]
            );

            // Generar PDF
            $pdfPath = $this->generatePdf($certificate, $user, $course, $templateName);

            // Actualizar certificado con ruta del PDF
            $certificate->update([
                'pdf_path' => $pdfPath,
                'pdf_filename' => basename($pdfPath),
                'pdf_size' => Storage::disk('public')->size($pdfPath),
                'status' => 'issued',
                'issued_at' => now(),
            ]);

            // Generar token de verificación
            $certificate->generateVerificationToken();

            return $certificate->fresh();
        } catch (Throwable $e) {
            \Log::error('Error generating certificate: ' . $e->getMessage(), [
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);

            throw $e;
        }
    }

    /**
     * Generar PDF del certificado
     */
    private function generatePdf(
        Certificate $certificate,
        User $user,
        Course $course,
        ?string $templateName = 'default'
    ): string {
        // Preparar datos para la vista
        $data = [
            'certificate' => $certificate,
            'user' => $user,
            'course' => $course,
            'issuedDate' => $certificate->issued_at ?? now(),
            'certificateNumber' => $certificate->certificate_number,
            'recipientName' => $user->name,
            'courseName' => $course->title,
            'finalScore' => $certificate->final_score,
        ];

        // Seleccionar template
        $view = match ($templateName) {
            'formal' => 'certificates.templates.formal',
            'modern' => 'certificates.templates.modern',
            'default' => 'certificates.templates.default',
            default => 'certificates.templates.default',
        };

        // Generar PDF
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'landscape')
            ->setOption('dpi', 300)
            ->setOption('enable_remote', true)
            ->setOption('isHtml5ParserEnabled', true);

        // Crear directorio si no existe
        $directory = 'certificates/' . now()->format('Y/m');
        Storage::disk('public')->makeDirectory($directory, 0755, true);

        // Generar nombre de archivo único
        $filename = sprintf(
            '%s_%s_%s.pdf',
            $certificate->certificate_number,
            slug($user->name),
            now()->timestamp
        );

        $filepath = $directory . '/' . $filename;

        // Guardar PDF
        Storage::disk('public')->put(
            $filepath,
            $pdf->output()
        );

        return $filepath;
    }

    /**
     * Obtener certificado descargable
     */
    public function getPdfForDownload(Certificate $certificate): ?string
    {
        if (!$certificate->pdf_path) {
            return null;
        }

        $path = Storage::disk('public')->path($certificate->pdf_path);

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Obtener URL de descarga del certificado
     */
    public function getDownloadUrl(Certificate $certificate): ?string
    {
        if (!$certificate->pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($certificate->pdf_path);
    }

    /**
     * Revocar un certificado
     */
    public function revokeCertificate(Certificate $certificate, string $reason = null): void
    {
        $certificate->revoke($reason);

        // Aquí podrías agregar lógica adicional como:
        // - Notificar al usuario
        // - Crear auditoría
        // - Enviar email de revocación
    }

    /**
     * Re-emitir certificado (si fue revocado)
     */
    public function reissue(Certificate $certificate): Certificate
    {
        if ($certificate->status === 'revoked') {
            $certificate->update([
                'status' => 'pending',
                'certificate_number' => Certificate::generateCertificateNumber(),
            ]);
        }

        return $this->generateCertificate(
            $certificate->enrollment,
            $certificate->user,
            $certificate->course,
            $certificate->final_score,
            $certificate->template_used
        );
    }

    /**
     * Obtener estadísticas de certificados por equipo
     */
    public function getTeamCertificateStats($teamId): array
    {
        $certificates = Certificate::where('team_id', $teamId)->get();

        return [
            'total' => $certificates->count(),
            'issued' => $certificates->where('status', 'issued')->count(),
            'pending' => $certificates->where('status', 'pending')->count(),
            'revoked' => $certificates->where('status', 'revoked')->count(),
            'verified' => $certificates->where('is_verified', true)->count(),
        ];
    }

    /**
     * Buscar certificados con filtros
     */
    public function searchCertificates(array $filters = []): \Illuminate\Pagination\Paginator
    {
        $query = Certificate::query();

        if (isset($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRelation('user', 'name', 'like', "%{$search}%")
                    ->orWhereRelation('course', 'title', 'like', "%{$search}%")
                    ->orWhere('certificate_number', 'like', "%{$search}%");
            });
        }

        if (isset($filters['issued_from'])) {
            $query->whereDate('issued_at', '>=', $filters['issued_from']);
        }

        if (isset($filters['issued_to'])) {
            $query->whereDate('issued_at', '<=', $filters['issued_to']);
        }

        return $query->latest('issued_at')->paginate($filters['per_page'] ?? 15);
    }
}
