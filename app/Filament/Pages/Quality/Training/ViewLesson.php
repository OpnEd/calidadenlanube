<?php

namespace App\Filament\Pages\Quality\Training;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Filament\Resources\Quality\Training\LessonResource;
use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\Lesson;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViewLesson extends Page
{
    protected static ?string $slug = 'quality/training/lessons/view';

    protected static string $view = 'filament.pages.quality.training.view-lesson';

    public Lesson $lesson;

    public ?Enrollment $enrollment = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $lessonId = request()->integer('lesson');

        abort_unless($lessonId, 404);

        $this->lesson = Lesson::query()
            ->with(['module.course', 'assessment'])
            ->findOrFail($lessonId);

        Gate::authorize('view', $this->lesson);

        $this->enrollment = $this->resolveEnrollment();
    }

    protected function resolveEnrollment(): ?Enrollment
    {
        $user = auth()->user();
        $courseId = $this->lesson->module?->course_id;
        $tenantId = Filament::getTenant()?->id;

        if (! $user || ! $courseId || ! $tenantId) {
            return null;
        }

        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('team_id', $tenantId)
            ->orderByRaw(
                'case when status = ? then 0 when status = ? then 1 else 2 end',
                [Enrollment::STATUS_IN_PROGRESS, Enrollment::STATUS_NOT_STARTED]
            )
            ->latest('id')
            ->first();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->lesson->title;
    }

    public function getHeading(): string|Htmlable
    {
        return $this->lesson->title;
    }

    public function getSubheading(): ?string
    {
        return collect([
            $this->lesson->module?->course?->title,
            $this->lesson->module?->title,
            $this->lesson->duration ? "{$this->lesson->duration} min" : null,
        ])->filter()->implode(' - ');
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];

        if ($course = $this->lesson->module?->course) {
            $breadcrumbs[\App\Filament\Resources\Quality\Training\CourseResource::getUrl('view', [
                'record' => $course,
            ])] = $course->title;
        }

        if ($module = $this->lesson->module) {
            $breadcrumbs[ModuleResource::getUrl('view', [
                'record' => $module,
            ])] = $module->title;
        }

        $breadcrumbs[static::getUrl([
            'lesson' => $this->lesson->getKey(),
        ])] = 'Vista de leccion';

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToModule')
                ->label('Volver al modulo')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ModuleResource::getUrl('view', [
                    'record' => $this->lesson->module,
                ])),

            Action::make('goToAssessment')
                ->label('Presentar evaluacion')
                ->icon('heroicon-o-academic-cap')
                ->color('warning')
                ->visible(fn (): bool => $this->lesson->requiresAssessment()
                    && $this->lesson->assessment !== null
                    && $this->enrollment !== null)
                ->url(fn (): string => EnrollmentResource::getUrl('lesson', [
                    'record' => $this->enrollment?->getKey(),
                    'lesson' => $this->lesson->getKey(),
                ])),
                
            Action::make('editLesson')
                ->label('Editar leccion')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('update', $this->lesson) ?? false)
                ->url(LessonResource::getUrl('edit', [
                    'record' => $this->lesson,
                ])),
        ];
    }

    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    public function getCompletionModeLabel(): string
    {
        return match ($this->lesson->completion_mode) {
            Lesson::COMPLETION_MODE_CONSUMPTION_ONLY => 'Solo consumo',
            Lesson::COMPLETION_MODE_ASSESSMENT_REQUIRED => 'Requiere evaluacion',
            default => 'No definido',
        };
    }

    public function getIntroduction(): ?string
    {
        return filled($this->lesson->introduction)
            ? (string) $this->lesson->introduction
            : null;
    }

    public function getObjectives(): array
    {
        return $this->normalizeStringList($this->lesson->objectives, ['objective', 'text', 'value']);
    }

    public function getContentBlocks(): array
    {
        return $this->normalizeStringList($this->lesson->content, ['content_item', 'content', 'text', 'body']);
    }

    public function getConclusions(): array
    {
        return $this->normalizeStringList($this->lesson->conclusions, ['conclusion', 'text', 'value']);
    }

    public function getReferences(): array
    {
        $references = $this->lesson->references;

        if (! is_array($references)) {
            return [];
        }

        return collect($references)
            ->map(function ($reference): ?array {
                if (is_string($reference) && filled(trim($reference))) {
                    return [
                        'text' => trim($reference),
                        'url' => null,
                    ];
                }

                if (! is_array($reference)) {
                    return null;
                }

                $text = trim((string) ($reference['text'] ?? $reference['label'] ?? ''));
                $url = trim((string) ($reference['url'] ?? ''));

                if (blank($text) && blank($url)) {
                    return null;
                }

                return [
                    'text' => $text !== '' ? $text : $url,
                    'url' => $url !== '' ? $url : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getIllustrationUrls(): array
    {
        $illustrations = $this->lesson->ilustrations;

        if (is_string($illustrations) && filled($illustrations)) {
            return [$this->resolveMediaUrl($illustrations)];
        }

        if (! is_array($illustrations)) {
            return [];
        }

        return collect($illustrations)
            ->map(fn ($path): ?string => is_string($path) ? $this->resolveMediaUrl($path) : null)
            ->filter()
            ->values()
            ->all();
    }

    public function getHeroImageUrl(): ?string
    {
        $illustration = $this->getIllustrationUrls()[0] ?? null;

        if ($illustration) {
            return $illustration;
        }

        if ($moduleImage = $this->lesson->module?->image) {
            return $this->resolveMediaUrl($moduleImage);
        }

        $courseImage = $this->lesson->module?->course?->image;

        if (blank($courseImage)) {
            return asset('storage/course_images/default_course.png');
        }

        return URL::to('storage/course_images/' . ltrim((string) $courseImage, '/'));
    }

    public function getVideoIframe(): ?string
    {
        $iframe = trim((string) ($this->lesson->iframe ?? ''));

        if ($iframe === '') {
            return null;
        }

        $iframe = preg_replace('/\s(width|height)=["\'][^"\']*["\']/', '', $iframe) ?? $iframe;

        if (! Str::contains($iframe, 'loading=')) {
            $iframe = preg_replace('/<iframe/i', '<iframe loading="lazy"', $iframe, 1) ?? $iframe;
        }

        if (! Str::contains($iframe, 'allowfullscreen')) {
            $iframe = preg_replace('/<iframe/i', '<iframe allowfullscreen', $iframe, 1) ?? $iframe;
        }

        if (! Str::contains($iframe, 'class=')) {
            return preg_replace('/<iframe/i', '<iframe class="h-full w-full"', $iframe, 1) ?? $iframe;
        }

        return $iframe;
    }

    public function getEmbeddedVideoUrl(): ?string
    {
        $videoUrl = trim((string) ($this->lesson->video_url ?? ''));

        if ($videoUrl === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([^&?/]+)~', $videoUrl, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $videoUrl, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return null;
    }

    public function getDirectVideoUrl(): ?string
    {
        $videoUrl = trim((string) ($this->lesson->video_url ?? ''));

        if ($videoUrl === '') {
            return null;
        }

        $resolvedUrl = $this->resolveMediaUrl($videoUrl);
        $extension = strtolower(pathinfo((string) parse_url($resolvedUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'm4v'], true)
            ? $resolvedUrl
            : null;
    }

    public function getExternalVideoUrl(): ?string
    {
        $videoUrl = trim((string) ($this->lesson->video_url ?? ''));

        if ($videoUrl === '') {
            return null;
        }

        if ($this->getEmbeddedVideoUrl() || $this->getDirectVideoUrl()) {
            return null;
        }

        return $this->resolveMediaUrl($videoUrl);
    }

    public function hasRichContent(): bool
    {
        return filled($this->getIntroduction())
            || $this->getObjectives() !== []
            || $this->getContentBlocks() !== []
            || $this->getConclusions() !== []
            || $this->getReferences() !== []
            || $this->getIllustrationUrls() !== [];
    }

    public function getSectionNavigation(): array
    {
        return collect([
            filled($this->getIntroduction()) ? ['id' => 'introduccion', 'label' => 'Introduccion'] : null,
            $this->getObjectives() !== [] ? ['id' => 'objetivos', 'label' => 'Objetivos'] : null,
            $this->getContentBlocks() !== [] ? ['id' => 'contenido', 'label' => 'Desarrollo'] : null,
            $this->getIllustrationUrls() !== [] ? ['id' => 'recursos-visuales', 'label' => 'Recursos visuales'] : null,
            $this->getConclusions() !== [] ? ['id' => 'conclusiones', 'label' => 'Conclusiones'] : null,
            $this->getReferences() !== [] ? ['id' => 'referencias', 'label' => 'Referencias'] : null,
        ])->filter()->values()->all();
    }

    protected function normalizeStringList(mixed $items, array $preferredKeys = []): array
    {
        if (blank($items)) {
            return [];
        }

        if (is_string($items)) {
            return [trim($items)];
        }

        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->map(function ($item) use ($preferredKeys): ?string {
                if (is_string($item) && filled(trim($item))) {
                    return trim($item);
                }

                if (! is_array($item)) {
                    return null;
                }

                foreach ($preferredKeys as $key) {
                    $value = $item[$key] ?? null;

                    if (is_string($value) && filled(trim($value))) {
                        return trim($value);
                    }
                }

                foreach ($item as $value) {
                    if (is_string($value) && filled(trim($value))) {
                        return trim($value);
                    }
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function resolveMediaUrl(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return Storage::url($path);
    }
}
