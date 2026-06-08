<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\EnrollmentCompleted;
use App\Models\Quality\Training\Certificate;
use App\Notifications\Quality\Training\CertificateIssuedNotification;
use App\Services\Quality\AuditService;
use App\Services\Quality\CertificateService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class GenerateCertificate implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        protected CertificateService $certificateService
    ) {}

    public function handle(EnrollmentCompleted $event): void
    {
        try {
            $event->enrollment->loadMissing('user', 'course');

            $certificate = $this->certificateService->generateCertificate(
                enrollment: $event->enrollment,
                user: $event->enrollment->user,
                course: $event->enrollment->course,
                finalScore: $event->finalScore,
                templateName: 'default'
            );

            $event->enrollment->update([
                'certificated_at' => $certificate->issued_at,
                'certificate_url' => $certificate->getPdfDownloadUrl(),
                'score_final' => $event->finalScore,
            ]);

            AuditService::logCreate(
                $certificate->team_id,
                'Certificate',
                $certificate->id,
                description: "Certificado emitido para '{$event->enrollment->course->title}'",
            );

            $this->notifyUser($event->enrollment, $certificate);

            \Log::info('Certificate generated successfully', [
                'certificate_id' => $certificate->id,
                'enrollment_id' => $event->enrollment->id,
                'user_id' => $event->enrollment->user_id,
            ]);
        } catch (Throwable $e) {
            \Log::error('Failed to generate certificate', [
                'enrollment_id' => $event->enrollment->id,
                'user_id' => $event->enrollment->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function notifyUser($enrollment, Certificate $certificate): void
    {
        try {
            $enrollment->user->notify(
                new CertificateIssuedNotification($certificate, $enrollment->course)
            );

            if (auth()->check() && auth()->id() === $enrollment->user_id) {
                Notification::make()
                    ->title('Certificado emitido')
                    ->body("Tu certificado para {$enrollment->course->title} ha sido generado.")
                    ->success()
                    ->send();
            }
        } catch (Throwable $e) {
            \Log::warning('Failed to notify user about certificate', [
                'user_id' => $enrollment->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
