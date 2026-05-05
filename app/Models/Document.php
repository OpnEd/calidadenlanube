<?php

namespace App\Models;

use App\Models\Quality\Documentation\DocumentAcknowledgment;
use App\Models\Quality\Documentation\DocumentApproval;
use App\Models\Quality\Documentation\DocumentDistribution;
use App\Models\Quality\Documentation\DocumentReview;
use App\Models\Quality\Documentation\DocumentVersion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'title',
        'process_id',
        'document_category_id',
        'owner_user_id',
        'code',
        'body',
        'slug',
        'status',
        'effective_at',
        'expires_at',
        'is_obsolete',
        'current_version_id',
        'records',
        'data',
    ];

    protected $casts = [
        'process_id' => 'integer',
        'document_type_id' => 'integer',
        'owner_user_id' => 'integer',
        'current_version_id' => 'integer',
        'validity' => 'date',
        'effective_at' => 'date',
        'expires_at' => 'date',
        'is_obsolete' => 'boolean',
        'records' => 'array',
        'data' => 'array',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->documentCategory();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'current_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DocumentReview::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(DocumentDistribution::class);
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(DocumentAcknowledgment::class);
    }

    public function getGeneratedCodeAttribute(): string
    {
        $category = $this->documentCategory
            ?? DocumentCategory::query()->find($this->document_category_id);
        $process = $this->process
            ?? Process::query()->find($this->process_id);
        $teamId = $this->team_id;

        $template = data_get($category?->data, 'code_format', '{category}-{process}-{year}-{seq:4}');
        $sequenceDigits = $this->extractSequenceDigits($template);
        $normalizedTemplate = preg_replace('/\{seq:\d+\}/', '{seq}', $template) ?? '{category}-{process}-{year}-{seq}';

        $tokens = $this->buildCodeTokens(
            categoryCode: $category?->code,
            processCode: $process?->code,
            teamId: $teamId
        );

        $prefixTemplate = str_replace('{seq}', '', $normalizedTemplate);
        $prefix = $this->renderCodeTemplate($prefixTemplate, array_merge($tokens, ['seq' => '']));
        $prefix = rtrim($prefix, '-_/ ');

        $sequence = static::withTrashed()
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->when(
                filled($prefix),
                fn ($query) => $query->where('code', 'like', $prefix . '%')
            )
            ->count() + 1;

        $tokens['seq'] = str_pad((string) $sequence, $sequenceDigits, '0', STR_PAD_LEFT);

        return $this->renderCodeTemplate($normalizedTemplate, $tokens);
    }

    public function codeMatchesCategoryFormat(?string $code = null): bool
    {
        $category = $this->documentCategory
            ?? DocumentCategory::query()->find($this->document_category_id);
        $pattern = data_get($category?->data, 'code_regex');

        if (blank($pattern)) {
            return true;
        }

        $codeToValidate = $code ?? $this->code;

        if (!is_string($codeToValidate) || $codeToValidate === '') {
            return false;
        }

        $delimitedPattern = $this->ensureRegexDelimiters((string) $pattern);
        $result = @preg_match($delimitedPattern, $codeToValidate);

        return $result === 1;
    }

    public function getCodeValidationMessageAttribute(): string
    {
        $category = $this->documentCategory
            ?? DocumentCategory::query()->find($this->document_category_id);

        return data_get(
            $category?->data,
            'code_regex_message',
            'El codigo generado no cumple el formato configurado para este tipo documental.'
        );
    }

    public function getStructuredContentAttribute(): array
    {
        $dataSections = is_array($this->data) ? $this->data : [];
        $bodySections = json_decode((string) ($this->body ?? ''), true);
        $bodySections = is_array($bodySections) ? $bodySections : [];

        $merged = array_merge($bodySections, $dataSections);

        $objectives = collect($merged['objectives'] ?? [])
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item['objective'] ?? null;
                }

                return $item;
            })
            ->filter()
            ->values()
            ->all();

        $procedure = collect($merged['procedure'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->values()
            ->all();

        return [
            'objectives' => $objectives,
            'general_conditions' => $merged['general_conditions'] ?? null,
            'definitions' => is_array($merged['definitions'] ?? null) ? $merged['definitions'] : [],
            'normative_references' => is_array($merged['normative_references'] ?? null) ? $merged['normative_references'] : [],
            'procedure' => $procedure,
        ];
    }

    private function extractSequenceDigits(string $template): int
    {
        if (preg_match('/\{seq:(\d+)\}/', $template, $matches)) {
            return max(1, (int) ($matches[1] ?? 4));
        }

        return 4;
    }

    private function buildCodeTokens(?string $categoryCode, ?string $processCode, ?int $teamId): array
    {
        $now = now();

        return [
            'category' => strtoupper(Str::slug($categoryCode ?? 'DOC', '')),
            'process' => strtoupper(Str::slug($processCode ?? 'PROC', '')),
            'team' => (string) ($teamId ?? '0'),
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
            'day' => $now->format('d'),
        ];
    }

    private function renderCodeTemplate(string $template, array $tokens): string
    {
        $rendered = $template;

        foreach ($tokens as $key => $value) {
            $rendered = str_replace('{' . $key . '}', (string) $value, $rendered);
        }

        return strtoupper(preg_replace('/\s+/', '', $rendered) ?? $rendered);
    }

    private function ensureRegexDelimiters(string $pattern): string
    {
        $firstChar = substr($pattern, 0, 1);

        if (in_array($firstChar, ['/', '#', '~'], true)) {
            return $pattern;
        }

        return '/' . str_replace('/', '\/', $pattern) . '/';
    }
}
