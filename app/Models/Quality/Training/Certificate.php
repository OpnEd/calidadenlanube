<?php

namespace App\Models\Quality\Training;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\CertificateFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'course_id',
        'team_id',
        'certificate_number',
        'title',
        'description',
        'issuer',
        'issued_at',
        'valid_until',
        'final_score',
        'status',
        'is_verified',
        'verification_token',
        'pdf_path',
        'pdf_filename',
        'pdf_size',
        'template_used',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
        'final_score' => 'float',
        'is_verified' => 'boolean',
        'pdf_size' => 'integer',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con Enrollment
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relación con User (estudiante)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación con Team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Verifica si el certificado es válido y no ha expirado
     */
    public function isValid(): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Verifica si el certificado está vencido
     */
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Genera un número único de certificado
     */
    public static function generateCertificateNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = self::whereDate('created_at', today())
            ->count() + 1;

        return sprintf('CERT-%s-%05d', $date, $sequence);
    }

    /**
     * Marca el certificado como emitido
     */
    public function markAsIssued(string $pdfPath = null): self
    {
        $this->update([
            'status' => 'issued',
            'issued_at' => now(),
            'pdf_path' => $pdfPath,
        ]);

        return $this->fresh();
    }

    /**
     * Revoca el certificado
     */
    public function revoke(string $reason = null): self
    {
        $this->update([
            'status' => 'revoked',
            'notes' => $reason,
        ]);

        return $this->fresh();
    }

    /**
     * Genera un token de verificación
     */
    public function generateVerificationToken(): string
    {
        $token = hash('sha256', $this->id . $this->certificate_number . random_bytes(32));

        $this->update(['verification_token' => $token]);

        return $token;
    }

    /**
     * Verifica un token de certificado
     */
    public static function verifyToken(string $token): ?self
    {
        return self::where('verification_token', $token)
            ->where('status', 'issued')
            ->first();
    }

    /**
     * Obtiene la URL para descargar el PDF
     */
    public function getPdfDownloadUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return route('certificates.download', ['certificate' => $this->id]);
    }
}
