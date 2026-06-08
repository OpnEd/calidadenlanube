<?php

namespace App\Notifications\Quality\Training;

use App\Models\Quality\Training\Certificate;
use App\Models\Quality\Training\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Certificate $certificate,
        protected Course $course
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("¡Tu Certificado está Listo! - {$this->course->title}")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Felicidades por completar el curso de capacitación.")
            ->line("**{$this->course->title}**")
            ->line("")
            ->line("Tu certificado de finalización ha sido generado y está listo para descargar.")
            ->when(
                $this->certificate->final_score,
                fn($message) => $message->line("**Puntuación Final:** " . number_format($this->certificate->final_score, 2) . "%")
            )
            ->line("")
            ->line("**Detalles del Certificado:**")
            ->line("- Número: {$this->certificate->certificate_number}")
            ->line("- Emisión: " . $this->certificate->issued_at->locale('es')->isoFormat('D MMMM YYYY'))
            ->line("")
            ->action('Descargar Certificado', route('certificates.download', ['certificate' => $this->certificate->id]))
            ->line("")
            ->line("También puedes acceder a tu certificado desde tu panel de control en cualquier momento.")
            ->line("")
            ->line("Gracias por tu compromiso con la capacitación continua.")
            ->salutation('Saludos cordiales,')
            ->markdown();
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'certificate_issued',
            'certificate_id' => $this->certificate->id,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'certificate_number' => $this->certificate->certificate_number,
            'issued_at' => $this->certificate->issued_at,
            'download_url' => route('certificates.download', ['certificate' => $this->certificate->id]),
        ];
    }
}
