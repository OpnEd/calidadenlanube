<div class="space-y-6">
    {{-- SECCIÓN DEL VIDEO/IFRAME --}}
    <x-filament::section>
        <x-slot name="heading">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $record->title }}</h1>
        </x-slot>

        @if ($record->iframe)
            {{-- Contenedor responsivo para mantener el aspect-ratio 16:9 del video --}}
            <div class="relative h-0 overflow-hidden rounded-lg shadow-md" style="padding-bottom: 56.25%;">
                <div class="absolute top-0 left-0 w-full h-full">
                    {!! $record->iframe !!}
                </div>
            </div>
        @else
            {{-- Placeholder mejorado para cuando no hay video --}}
            <div class="flex flex-col items-center justify-center p-12 text-center bg-gray-50 rounded-lg dark:bg-gray-800/50">
                <x-filament::icon icon="heroicon-o-video-camera-slash"
                    class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                <p class="mt-4 text-lg font-medium text-gray-500 dark:text-gray-400">
                    No hay video disponible para esta lección.
                </p>
            </div>
        @endif
    </x-filament::section>

    {{-- SECCIÓN DE CONTENIDO Y METADATOS --}}
    <x-filament::section>
        {{-- Metadatos de la lección en una cuadrícula --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="text-sm">
                <dt class="font-semibold text-gray-900 dark:text-white">Módulo</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-300">{{ optional($record->module)->title ?? '—' }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-semibold text-gray-900 dark:text-white">Duración</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-300">{{ $record->duration }} minutos</dd>
            </div>
            <div class="text-sm">
                <dt class="font-semibold text-gray-900 dark:text-white">Orden</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-300">{{ $record->order }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-semibold text-gray-900 dark:text-white">Estado</dt>
                <dd class="mt-1">
                    @if (!empty($lessonStatus))
                        <x-filament::badge :color="$lessonStatus['color']">
                            {{ $lessonStatus['text'] }}
                        </x-filament::badge>
                    @endif
                </dd>
            </div>
        </div>

        {{-- Objetivo y Descripción --}}
        @if ($record->objective)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Objetivo</h3>
                <div class="mt-2 prose max-w-none dark:prose-invert">
                    <p>{!! nl2br(e($record->objective)) !!}</p>
                </div>
            </div>
        @endif

        @if ($record->description)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Descripción</h3>
                <div class="mt-2 prose max-w-none dark:prose-invert">
                    <p>{!! nl2br(e($record->description)) !!}</p>
                </div>
            </div>
        @endif
    </x-filament::section>

    {{-- Contenido principal de la lección (si existe) --}}
    @if ($record->content)
        <x-filament::section>
            <x-slot name="heading">
                Contenido de la Lección
            </x-slot>
            {{-- Se asume que el contenido HTML ha sido sanitizado antes de guardarlo para prevenir ataques XSS. --}}
            <div class="prose max-w-none dark:prose-invert">
                @if (is_array($record->content))
                    @if (isset($record->content['text']))
                        {!! $record->content['text'] !!}
                    @else
                        @foreach ($record->content as $block)
                            {!! $block['html'] ?? '' !!}
                        @endforeach
                    @endif
                @else
                    {!! $record->content !!}
                @endif
            </div>
        </x-filament::section>
    @endif

    {{-- NAVEGACIÓN ANTERIOR / SIGUIENTE MEJORADA --}}
    <div class="flex items-center justify-between pt-4 mt-8 border-t dark:border-white/10">
        <x-filament::button wire:click="previous" icon="heroicon-o-arrow-left" color="gray" :disabled="!$hasPreviousLesson">
            Anterior
        </x-filament::button>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Lección {{ $record->order }} de {{ $totalLessons }}
        </div>
        <x-filament::button wire:click="next" icon="heroicon-o-arrow-right" icon-position="after" :disabled="!$hasNextLesson">
            Siguiente
        </x-filament::button>
    </div>
</div>
