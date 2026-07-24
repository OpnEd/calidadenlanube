<div class="space-y-6">
    {{-- Sección de Información del Curso --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                Objetivo
            </span>
        </x-slot>

        <x-slot name="description">
            <span class="text-base text-gray-500 dark:text-gray-400">
                {{ $course->objective }}
            </span>
        </x-slot>

        <div class="prose dark:prose-invert max-w-full text-sm text-gray-600 dark:text-gray-300 mt-4 rounded-xl bg-gray-50/50 dark:bg-gray-900/50 p-5 border border-gray-100 dark:border-white/5">
        <h6 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Descripción</h6>
            {!! str($course->description)->markdown() !!}
        </div>
    </x-filament::section>

    {{-- Sección de Módulos y Lecciones --}}
    @if ($record)
        <div class="space-y-4 mt-8">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    Contenido del curso
                </h2>
                <span class="text-sm font-medium text-gray-500">
                    {{ $course->modules->count() }} {{ \Illuminate\Support\Str::plural('módulo', $course->modules->count()) }}
                </span>
            </div>

            @forelse ($course->modules as $index => $module)
                {{-- Mantenemos el componente nativo de Filament, colapsando todos menos el primero --}}
                <x-filament::section collapsible :collapsed="$index !== 0">
                    
                    {{-- Cabecera del Módulo Personalizada --}}
                    <x-slot name="heading">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-950/50 text-sm font-bold text-primary-600 dark:text-primary-400 ring-1 ring-primary-600/20 dark:ring-primary-400/20">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-base font-bold">{{ $module->title }}</span>
                        </div>
                    </x-slot>

                    <x-slot name="headerEnd">
                        <x-filament::badge color="gray">
                            {{ $module->lessons->count() }} {{ \Illuminate\Support\Str::plural('lección', $module->lessons->count()) }}
                        </x-filament::badge>
                    </x-slot>

                    {{-- Detalles del Módulo --}}
                    @if($module->objective || $module->description)
                        <div class="mb-6 space-y-4">
                            @if($module->objective)
                                <div>
                                    <h6 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Objetivo</h6>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $module->objective }}</p>
                                </div>
                            @endif
                            @if($module->description)
                                <div>
                                    <h6 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Descripción</h6>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $module->description }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Lista de Lecciones Interactiva --}}
                    <div class="divide-y divide-gray-100 dark:divide-white/5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
                        @foreach ($module->lessons as $lesson)
                            @php
                                $status = $lessonStatuses[$lesson->id] ?? ['text' => 'No cursada', 'color' => 'gray'];
                                $lessonUrl = $lesson->active 
                                    ? \App\Filament\Resources\Quality\Training\EnrollmentResource::getUrl('lesson', [
                                        'record' => $record->getKey(),
                                        'lesson' => $lesson->id,
                                    ])
                                    : null;
                            @endphp

                            @if ($lessonUrl)
                                <a href="{{ $lessonUrl }}" target="_blank" rel="noopener noreferrer" 
                                   class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 py-3.5 transition hover:bg-gray-50 dark:hover:bg-white/5 group">
                            @else
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 py-3.5 opacity-75 bg-gray-50/50 dark:bg-gray-800/30">
                            @endif

                                {{-- Icono y Título --}}
                                <div class="flex items-center gap-3">
                                    <x-filament::icon 
                                        icon="{{ $lessonUrl ? 'heroicon-o-play-circle' : 'heroicon-o-lock-closed' }}" 
                                        class="h-5 w-5 {{ $lessonUrl ? 'text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400' : 'text-gray-400' }} transition" 
                                    />
                                    <span class="text-sm font-medium {{ $lessonUrl ? 'text-gray-700 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400' : 'text-gray-500 dark:text-gray-400' }} transition">
                                        {{ $lesson->title }}
                                    </span>
                                </div>

                                {{-- Badges de Estado y Flecha --}}
                                <div class="flex items-center gap-3 self-end sm:self-auto">
                                    
                                    {{-- Utilizamos el Status generado por tu componente Livewire --}}
                                    <x-filament::badge :color="$status['color']" class="flex-shrink-0">
                                        {{ $status['text'] }}
                                    </x-filament::badge>

                                    @if ($lessonUrl)
                                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition hidden sm:block" />
                                    @endif
                                </div>

                            @if ($lessonUrl)
                                </a>
                            @else
                                </div>
                            @endif
                        @endforeach
                    </div>

                </x-filament::section>
            @empty
                <x-filament::section icon="heroicon-o-x-circle">
                    <x-slot name="heading">
                        ¡Aún no hay contenido!
                    </x-slot>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Este curso no tiene módulos asignados por el momento.
                    </p>
                </x-filament::section>
            @endforelse
        </div>
    @endif
</div>