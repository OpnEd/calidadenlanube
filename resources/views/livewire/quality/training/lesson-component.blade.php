<div class="space-y-8">

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        
        <div class="space-y-6 lg:col-span-8 xl:col-span-9 overflow-hidden">

            @if ($lesson->iframe)
                <x-filament::section>
                    <div class="relative h-0 bg-black" style="padding-bottom: 56.25%;">
                        <div
                            class="absolute inset-0 flex h-full w-full items-center justify-center [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:max-w-full [&>iframe]:mx-auto">
                            {!! $lesson->iframe !!}
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    <div
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center dark:border-white/10 dark:bg-gray-900/50">
                        <x-filament::icon icon="heroicon-o-video-camera"
                            class="h-16 w-16 text-gray-300 dark:text-gray-600" />
                        <p class="mt-4 text-base font-medium text-gray-600 dark:text-gray-400">
                            No hay video disponible para esta lección.
                        </p>
                    </div>
                </x-filament::section>
            @endif

            <x-filament::section icon="heroicon-o-document-magnifying-glass" icon-color="info" collapsible collapsed>
                {{-- Agregamos un título para que la sección no se vea vacía al estar colapsada --}}
                <x-slot name="heading">
                    Detalles de la lección
                </x-slot>

                {{-- Grid de 2 columnas para el layout, con espacio entre los items --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:items-center">

                    {{-- Curso --}}
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Curso
                        </span>
                        <span class="text-sm text-gray-950 dark:text-white leading-tight">
                            {{ $lesson->module?->course?->title ?? 'N/A' }}
                        </span>
                    </div>

                    {{-- Módulo --}}
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Módulo
                        </span>
                        <span class="text-sm text-gray-950 dark:text-white leading-tight">
                            {{ $lesson->module?->title ?? 'N/A' }}
                        </span>
                    </div>

                    {{-- Modalidad --}}
                    <div class="flex flex-col gap-1.5 items-start">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Modalidad
                        </span>
                        @if ($lesson->isConsumptionOnly())
                            <x-filament::badge color="info" size="sm">
                                Solo Consumo
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="info" size="sm">
                                Requiere Evaluación
                            </x-filament::badge>
                        @endif

                    </div>

                    {{-- Estado --}}
                    @if (!empty($lessonStatus))
                        <div class="flex flex-col gap-1.5 items-start">
                            <span
                                class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Estado
                            </span>
                            <x-filament::badge :color="$lessonStatus['color']" size="sm">
                                {{ $lessonStatus['text'] }}
                            </x-filament::badge>
                        </div>
                    @endif

                </div>
            </x-filament::section>
            {{ $this->lessonInfolist }}
        </div>
        
        <x-filament::section icon="phosphor-question" icon-color="info" collapsible collapsed>
            <x-slot name="heading">
                Estado de la lección y progreso del curso
            </x-slot>

            {{-- Contenedor principal usando Grid (1 columna en móvil, 2 en pantallas medianas/grandes) --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:items-center">

                <div class="flex flex-col space-y-4">

                    {{-- Caja de estado --}}
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 dark:bg-gray-800/50">
                        @if ($lessonConsumed)
                            <div class="flex items-center justify-between w-full">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Contenido revisado
                                </p>
                                <x-filament::icon icon="heroicon-o-check-circle"
                                    class="h-6 w-6 text-green-600 dark:text-green-400 shrink-0" />
                            </div>
                        @else
                            <div class="flex items-center justify-between w-full gap-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Marca esta lección cuando termines de consumir el material.
                                </p>
                                <x-filament::icon icon="heroicon-o-clock"
                                    class="h-6 w-6 text-amber-500 dark:text-amber-400 shrink-0" />
                            </div>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <x-filament::button wire:click="markLessonConsumed" :disabled="$lessonConsumed"
                        color="{{ $lessonConsumed ? 'success' : 'info' }}"
                        class="w-full rounded-xl px-4 py-3 text-sm font-semibold transition">
                        {{ $lessonConsumed ? 'Lección consumida' : 'Marcar como consumida' }}
                    </x-filament::button>

                </div>

                {{-- COLUMNA DERECHA: Gráfica de Livewire --}}
                <div class="relative w-100 h-48">
                    @livewire(\App\Livewire\Quality\Training\CourseProgressChart::class, [
                        'progress' => $enrollment->progress ?? 0,
                    ])
                </div>

            </div>
        </x-filament::section>

        @if ($assessment)

            <x-filament::section icon="phosphor-pencil-simple" icon-color="danger" collapsible collapsed>
                <x-slot name="heading">
                    Evaluación
                </x-slot>

                <div class="space-y-4">
                    <div class="rounded-2xl bg-blue-50 p-4 dark:bg-blue-900/20">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                                    @if ($assessment->max_attempts)
                                        {{ $remainingAttempts }} de {{ $assessment->max_attempts }} intentos
                                        disponibles
                                    @else
                                        Intentos ilimitados
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                                    {{ $assessment->title }}
                                </p>
                            </div>
                            <x-filament::icon icon="heroicon-o-academic-cap"
                                class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    @if ($assessment->duration_minutes)
                        <div class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-900/20">
                            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                Tiempo limite: {{ $assessment->duration_minutes }} minutos
                            </p>
                        </div>
                    @endif

                    @if ($latestAttempt)
                        <div class="rounded-2xl border border-gray-200 p-4 dark:border-white/10">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                Último intento
                            </p>
                            <div class="mt-3 flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Puntuación</p>
                                    <p class="text-2xl font-semibold text-gray-950 dark:text-white">
                                        {{ number_format($latestAttempt->score ?? 0, 1) }}
                                    </p>
                                </div>

                                @if ($latestAttempt->isPassed())
                                    <x-filament::badge color="success">Aprobada</x-filament::badge>
                                @else
                                    <x-filament::badge color="danger">No aprobada</x-filament::badge>
                                @endif
                            </div>
                        </div>
                    @endif

                    <button wire:click="toggleAssessmentForm" @class([
                        'w-full rounded-xl px-4 py-3 text-sm font-semibold text-white transition',
                        'bg-primary-600 hover:bg-primary-500' => $assessmentCanStart,
                        'cursor-not-allowed bg-gray-400 dark:bg-gray-600' => !$assessmentCanStart,
                    ]) @disabled(!$assessmentCanStart)>
                        {{ $showAssessment ? 'Cerrar evaluación' : ($assessmentCanStart ? 'Comenzar evaluación' : 'Evaluación no disponible') }}
                    </button>

                    @if (!$assessmentCanStart && $assessmentStartError)
                        <div
                            class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800/50 dark:text-gray-300">
                            {{ $assessmentStartError }}
                        </div>
                    @endif
                </div>
            </x-filament::section>

            @if ($showAssessment && $assessmentCanStart)
                <x-filament::section>
                    <x-slot name="heading">
                        Presentar evaluación
                    </x-slot>
                    @livewire('quality.training.assessment-component', ['assessment' => $assessment, 'enrollment' => $enrollment], key('assessment-' . $assessment->id))
                </x-filament::section>
            @endif
        @else
            @if ($lesson->isConsumptionOnly())
                <x-filament::section>
                    <div class="rounded-2xl bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                        <x-filament::icon icon="heroicon-o-information-circle"
                            class="mx-auto h-8 w-8 text-blue-600 dark:text-blue-400" />
                        <p class="mt-2 text-sm font-medium text-blue-900 dark:text-blue-200">
                            Esta lección es solo de consumo, no requiere evaluación.
                        </p>
                    </div>
                </x-filament::section>
            @endif

            @if ($lesson->requiresAssessment())
                <x-filament::section>
                    <div class="rounded-2xl bg-green-50 p-4 text-center dark:bg-green-900/20">
                        <x-filament::icon icon="heroicon-o-check-circle"
                            class="mx-auto h-8 w-8 text-green-600 dark:text-green-400" />
                        <p class="mt-2 text-sm font-medium text-green-900 dark:text-green-200">
                            Esta lección requiere evaluación pero aún no tiene evaluación asociada.
                        </p>
                    </div>
                </x-filament::section>
            @endif

        @endif

    </div>

    <div><br></div>

    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-filament::button wire:click="previous" icon="heroicon-o-arrow-left" color="gray" size="lg"
                :disabled="!$hasPreviousLesson">
                Lección anterior
            </x-filament::button>

            <p class="text-center text-sm font-medium text-gray-600 dark:text-gray-300">
                Lección {{ $currentLessonPosition }} de {{ $totalLessons }}
            </p>

            <x-filament::button wire:click="next" icon-position="after" icon="heroicon-o-arrow-right" size="lg"
                :disabled="!$hasNextLesson">
                Lección siguiente
            </x-filament::button>
        </div>
    </x-filament::section>
</div>
