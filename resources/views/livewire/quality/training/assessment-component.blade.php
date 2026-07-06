<div class="space-y-6">
    @if (!$currentAttempt)
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin">
                <x-filament::icon icon="heroicon-o-arrow-path" class="h-8 w-8 text-primary-600 dark:text-primary-400" />
            </div>
        </div>
    @else
        @if ($assessment->duration_minutes)
            <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/30">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    <div>
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                            Tiempo limite
                        </p>
                        <p class="text-xs text-amber-700 dark:text-amber-300">
                            {{ $assessment->duration_minutes }} minutos para completar esta evaluacion
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (!$showResults)
            <form wire:submit="submitAssessment" class="space-y-8">
                @foreach ($questions as $index => $question)
                    <div class="rounded-lg border border-gray-200 p-6 dark:border-white/10">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-800 dark:bg-primary-900/30 dark:text-primary-200">
                                        Pregunta {{ $index + 1 }} de {{ $questionsCount }}
                                    </span>
                                    @if ($question->isRequired())
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-200">
                                            Obligatoria
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $question->question_text }}
                                </h3>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @if ($question->isMultipleChoiceSingle() || $question->isTrueFalse())
                                <div class="space-y-2">
                                    @foreach ($question->questionOptions as $option)
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-gray-800/50"
                                            wire:key="single-option-{{ $question->id }}-{{ $option->id }}"
                                        >
                                            <input
                                                type="radio"
                                                name="question_{{ $question->id }}"
                                                value="{{ $option->id }}"
                                                wire:model="userAnswers.{{ $question->id }}"
                                                class="mt-1 h-4 w-4 border-gray-300 text-primary-600 dark:border-white/20 dark:bg-gray-700 dark:checked:bg-primary-600"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->isMultipleChoiceMultiple())
                                <div class="space-y-2">
                                    @foreach ($question->questionOptions as $option)
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-gray-800/50"
                                            wire:key="multi-option-{{ $question->id }}-{{ $option->id }}"
                                        >
                                            <input
                                                type="checkbox"
                                                value="{{ $option->id }}"
                                                wire:model="userAnswers.{{ $question->id }}"
                                                class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-600 dark:border-white/20 dark:bg-gray-700 dark:checked:bg-primary-600"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->isFreeText())
                                <textarea
                                    wire:model="userAnswers.{{ $question->id }}"
                                    placeholder="Escribe tu respuesta aqui..."
                                    rows="4"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/20 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-400"
                                ></textarea>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-white/10">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="cancelAttempt"
                        :disabled="$isSubmitting"
                    >
                        Cancelar
                    </x-filament::button>

                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ count(array_filter($userAnswers, fn ($answer) => filled($answer))) }} de {{ $questionsCount }} respondidas
                        </span>
                        <x-filament::button
                            type="submit"
                            :disabled="$isSubmitting"
                            wire:loading.attr="disabled"
                        >
                            {{ $isSubmitting ? 'Procesando...' : 'Enviar Evaluación' }}
                        </x-filament::button>
                    </div>
                </div>
            </form>
        @else
            <div class="space-y-6">
                <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 p-8 text-white dark:from-primary-600 dark:to-primary-800">
                    <div class="relative z-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-wider opacity-90">Resultado</p>
                        <p class="mt-2 text-5xl font-bold">
                            {{ number_format($results['score'], 1) }}/{{ number_format($results['max_score'], 1) }}
                        </p>
                        <p class="mt-2 text-lg font-semibold opacity-90">
                            {{ number_format($results['score_percentage'], 1) }}%
                        </p>
                        <p class="mt-4 text-base font-semibold">
                            {{ $results['passed'] ? 'Aprobado' : 'No aprobado' }}
                        </p>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-1/2 opacity-10">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-full w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Correctas</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $results['correct_answers'] }}/{{ $results['gradable_questions'] }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Duracion</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $results['duration'] !== null ? $results['duration'] . 'm' : '--' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Preguntas</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $results['total_questions'] }}</p>
                    </div>
                </div>

                @if ($results['feedback'])
                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/30">
                        <p class="text-sm text-blue-900 dark:text-blue-200">
                            {{ $results['feedback'] }}
                        </p>
                    </div>
                @endif

                <div class="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-white/10">
                    <x-filament::button
                        color="gray"
                        wire:click="$refresh"
                    >
                        Cerrar
                    </x-filament::button>

                    @if ($remainingAttempts === null || $remainingAttempts > 0)
                        <x-filament::button
                            color="primary"
                            wire:click="startAttempt"
                        >
                            Intentar de Nuevo
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
