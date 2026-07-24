<div class="space-y-8">

    @if (!empty($breadcrumbs))
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
    @endif

    <div>
        <br>
    </div>

    <x-filament::section>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-sm font-medium">
                Modulo {{ $lesson->module?->order ?? '-' }}
            </span>
            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-sm font-medium">
                Leccion {{ $currentLessonPosition }}/{{ $totalLessons }}
            </span>
            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-sm font-medium">
                Modalidad &nbsp;
                @if ($lesson->isConsumptionOnly())
                    <p class="text-sm font-semibold"> Consumo</p>
                @else
                    <p class="text-sm font-semibold"> Evaluacion</p>
                @endif
            </span>
            @if (!empty($lessonStatus))
                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-sm font-medium">
                    Estado de la leccion:
                </span>
                <x-filament::badge :color="$lessonStatus['color']">
                    {{ $lessonStatus['text'] }}
                </x-filament::badge>
            @endif
        </div>

    </x-filament::section>

    <div>
        <br>
    </div>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.85fr)_minmax(20rem,0.95fr)]">
        {{-- Iframe --}}
        <x-filament::section>

            @if ($lesson->iframe)
                <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-white/10">
                    <div class="relative h-0 bg-black" style="padding-bottom: 56.25%;">
                        <div class="absolute inset-0 h-full w-full">
                            {!! $lesson->iframe !!}
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center dark:border-white/10 dark:bg-gray-900/50">
                    <x-filament::icon icon="heroicon-o-video-camera"
                        class="h-16 w-16 text-gray-300 dark:text-gray-600" />
                    <p class="mt-4 text-base font-medium text-gray-600 dark:text-gray-400">
                        No hay video disponible para esta leccion.
                    </p>
                </div>
            @endif
        </x-filament::section>

        {{-- Descripción de la lección --}}
        <x-filament::section>
            {{-- Subtítulo: Descripción --}}
            <h3 class="text-lg font-semibold">Descripción</h3>
            @if ($lesson->description)
                <div>
                    <p class="whitespace-pre-line">
                        {{ $lesson->description }}
                    </p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay descripción disponible para esta leccion.
                    </p>
                </div>
            @endif
        </x-filament::section>

        {{-- Objetivos de aprendizaje e introducción --}}
        <x-filament::section>

            <div class="prose max-w-none dark:prose-invert space-y-6">

                {{-- Subtítulo: Objetivos --}}
                <h3 class="text-lg font-semibold">Objetivos de aprendizaje</h3>
                @if (!empty($lesson->objectives))
                    <div>
                        <ol class="list-decimal pl-5 space-y-1">
                            @foreach ($lesson->objectives as $objective)
                                @if (is_array($objective) && isset($objective['objective']))
                                    <li>{{ $objective['objective'] }}</li>
                                @else
                                    <li>{{ $objective }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </div>
                @else
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No hay objetivos de aprendizaje disponibles para esta leccion.
                        </p>
                    </div>
                @endif
                {{-- Subtítulo: Introducción --}}
                <h3 class="text-lg font-semibold">Introducción</h3>
                @if ($lesson->introduction)
                    <div>
                        <p class="whitespace-pre-line">
                            {{ $lesson->introduction }}
                        </p>
                    </div>
                @else
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No hay introducción disponible para esta leccion.
                        </p>
                    </div>
                @endif

            </div>

        </x-filament::section>

        {{-- Material de estudio (contenido) --}}
        <x-filament::section>

            <x-slot name="heading">
                Material de estudio
            </x-slot>
            @if ($lesson->content)

                <div class="prose max-w-none dark:prose-invert">
                    @if (is_array($lesson->content))
                        @if (isset($lesson->content['text']))
                            {!! $lesson->content['text'] !!}
                        @else
                            @foreach ($lesson->content as $block)
                                {!! $block['html'] ?? ($block['text'] ?? '') !!}
                            @endforeach
                        @endif
                    @else
                        {!! $lesson->content !!}
                    @endif
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay material de estudio disponible para esta lección.
                    </p>
                </div>

            @endif

        </x-filament::section>

        {{-- Conclusiones --}}
        <x-filament::section>

            {{-- Subtítulo: Conclusiones --}}
            <h3 class="text-lg font-semibold">Conclusiones</h3>
            @if (!empty($lesson->conclusions))

                <div class="prose max-w-none dark:prose-invert space-y-6">
                    <div>
                        <ol class="list-decimal pl-5 space-y-1">
                            @foreach ($lesson->conclusions as $conclusion)
                                @if (is_array($conclusion) && isset($conclusion['conclusion']))
                                    <li>{{ $conclusion['conclusion'] }}</li>
                                @else
                                    <li>{{ $conclusion }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </div>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay conclusiones disponibles para esta lección.
                    </p>
                </div>

            @endif

        </x-filament::section>

        {{-- Referencias --}}
        <x-filament::section>

            {{-- Subtítulo: Referencias --}}
            <h3 class="text-lg font-semibold">Referencias</h3>
            @if (!empty($lesson->references))
                <div class="prose max-w-none dark:prose-invert space-y-6">
                    <div>
                        <ol class="list-decimal pl-5 space-y-1">
                            @foreach ($lesson->references as $reference)
                                @if (is_array($reference) && isset($reference['reference']))
                                    <li>{{ $reference['reference'] }}</li>
                                @else
                                    <li>{{ $reference }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </div>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay referencias disponibles para esta lección.
                    </p>
                </div>
            @endif

        </x-filament::section>

        <aside class="space-y-6">

            <x-filament::section>
                <x-slot name="heading">
                    Estado de la lección
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3 dark:bg-gray-800/50">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Contenido revisado</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Marca esta lección cuando termines de consumir el material.
                            </p>
                        </div>

                        @if ($lessonConsumed)
                            <x-filament::icon icon="heroicon-o-check-circle"
                                class="h-6 w-6 text-green-600 dark:text-green-400" />
                        @else
                            <x-filament::icon icon="heroicon-o-clock"
                                class="h-6 w-6 text-amber-500 dark:text-amber-400" />
                        @endif
                    </div>

                    <x-filament::button wire:click="markLessonConsumed" :disabled="$lessonConsumed"
                        color="{{ $lessonConsumed ? 'gray' : 'success' }}"
                        class="w-full rounded-xl px-4 py-3 text-sm font-semibold transition">
                        {{ $lessonConsumed ? 'Lección consumida' : 'Marcar como consumida' }}
                    </x-filament::button>
                </div>
            </x-filament::section>
            @if ($assessment)
                <x-filament::section>
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
                                    Ultimo intento
                                </p>

                                <div class="mt-3 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Puntuacion</p>
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
                        ])
                            @disabled(!$assessmentCanStart)>
                            {{ $showAssessment ? 'Cerrar evaluacion' : ($assessmentCanStart ? 'Comenzar evaluacion' : 'Evaluacion no disponible') }}
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
                            Presentar evaluacion
                        </x-slot>

                        @livewire('quality.training.assessment-component', ['assessment' => $assessment, 'enrollment' => $enrollment], key('assessment-' . $assessment->id))
                    </x-filament::section>
                @endif
            @else
                <div><br></div>
                <x-filament::section>
                    <div class="rounded-2xl bg-green-50 p-4 text-center dark:bg-green-900/20">
                        <x-filament::icon icon="heroicon-o-check-circle"
                            class="mx-auto h-8 w-8 text-green-600 dark:text-green-400" />
                        <p class="mt-2 text-sm font-medium text-green-900 dark:text-green-200">
                            Esta leccion no tiene evaluacion asociada.
                        </p>
                    </div>
                </x-filament::section>
            @endif

            <x-filament::section>
                <x-slot name="heading">
                    Progreso del curso
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Avance general</span>
                        <span
                            class="font-semibold text-gray-950 dark:text-white">{{ $enrollment->progress ?? 0 }}%</span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600"
                            style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $currentLessonPosition }} de {{ $totalLessons }} lecciones recorridas en esta navegacion.
                    </p>
                </div>
            </x-filament::section>
        </aside>
    </div>

    <div><br></div>

    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <x-filament::button wire:click="previous" icon="heroicon-o-arrow-left" color="gray" size="lg"
                :disabled="!$hasPreviousLesson">
                Leccion anterior
            </x-filament::button>

            <p class="text-center text-sm font-medium text-gray-600 dark:text-gray-300">
                Leccion {{ $currentLessonPosition }} de {{ $totalLessons }}
            </p>

            <x-filament::button wire:click="next" icon-position="after" icon="heroicon-o-arrow-right" size="lg"
                :disabled="!$hasNextLesson">
                Leccion siguiente
            </x-filament::button>
        </div>
    </x-filament::section>
</div>
