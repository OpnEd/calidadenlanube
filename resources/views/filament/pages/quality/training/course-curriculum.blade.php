@php
    $record = $getRecord();

    $modules = $record->modules()
        ->with(['lessons' => fn ($q) => $q->where('lessons.active', true)])
        ->get();

    $enrollment = \App\Models\Quality\Training\Enrollment::where('course_id', $record->id)
        ->where('user_id', auth()->id())
        ->first();
@endphp

<div class="space-y-4">
    @forelse ($modules as $index => $module)
        {{-- 
            Lógica de expansión: 
            Si está inscrito, abre el primero por defecto. 
            Si NO está inscrito (modo venta/preview), todos arrancan cerrados para no saturar la pantalla.
        --}}
        <div x-data="{ isExpanded: {{ ($index === 0 && $enrollment) ? 'true' : 'false' }} }" 
             class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 shadow-sm">
            
            {{-- Header del Módulo (Botón de despliegue) --}}
            <div @click="isExpanded = !isExpanded" 
                 class="group cursor-pointer bg-gray-50/80 p-4 dark:bg-gray-800/50 flex items-start gap-4 border-b border-gray-200/60 dark:border-white/5 hover:bg-gray-100/80 dark:hover:bg-gray-800 transition">
                
                {{-- Columna Izquierda: Índice --}}
                <div class="flex-shrink-0 pt-0.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-950/50 text-sm font-bold text-primary-600 dark:text-primary-400 ring-1 ring-primary-600/20 dark:ring-primary-400/20">
                        {{ $index + 1 }}
                    </span>
                </div>

                {{-- Columna Derecha: Información del Módulo --}}
                <div class="flex flex-1 flex-col min-w-0">
                    
                    {{-- Fila superior: Título, Conteo y Chevron --}}
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                            {{ $module->title }}
                        </h4>
                        
                        <div class="flex items-center gap-3">
                            <span class="flex-shrink-0 inline-flex items-center rounded-full bg-white dark:bg-gray-900 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-white/10 shadow-sm">
                                {{ $module->lessons->count() }} {{ \Illuminate\Support\Str::plural('lección', $module->lessons->count()) }}
                            </span>
                            
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-200/50 dark:bg-gray-700/50 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/50 transition">
                                <x-filament::icon 
                                    icon="heroicon-m-chevron-down" 
                                    class="h-4 w-4 text-gray-500 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition duration-300" 
                                    x-bind:class="{ 'rotate-180': isExpanded }" 
                                />
                            </div>
                        </div>
                    </div>

                    {{-- 
                        Mostramos los detalles largos (Objetivo y Descripción) 
                        SOLO si el usuario ya compró/está inscrito en el curso.
                    --}}
                    @if ($enrollment)
                        <div class="space-y-4">
                            @if($module->objective)
                                <div>
                                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Objetivo
                                    </h6>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                        {{ $module->objective }}
                                    </p>
                                </div>
                            @endif

                            @if($module->description)
                                <div>
                                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Descripción
                                    </h6>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                        {{ $module->description }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                </div>
            </div>

            {{-- Lista de Lecciones (Contenedor Colapsable) --}}
            <div x-show="isExpanded" x-collapse>
                <div class="divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-gray-900">
                    @forelse ($module->lessons as $lesson)
                        @php
                            $lessonUrl = $enrollment 
                                ? \App\Filament\Resources\Quality\Training\EnrollmentResource::getUrl('lesson', [
                                    'record' => $enrollment->getKey(), 
                                    'lesson' => $lesson->getKey()
                                ])
                                : null;
                        @endphp

                        @if ($lessonUrl)
                            <a href="{{ $lessonUrl }}" 
                               class="flex items-center justify-between px-6 py-3.5 transition hover:bg-gray-50 dark:hover:bg-white/5 group">
                        @else
                            <div class="flex items-center justify-between px-6 py-3.5 opacity-80 bg-gray-50/30 dark:bg-gray-800/30">
                        @endif

                            <div class="flex items-center gap-3">
                                <x-filament::icon 
                                    icon="heroicon-o-play-circle" 
                                    class="h-5 w-5 {{ $lessonUrl ? 'text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400' : 'text-gray-300 dark:text-gray-600' }} transition" 
                                />
                                
                                <span class="text-sm font-medium {{ $lessonUrl ? 'text-gray-700 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400' : 'text-gray-500 dark:text-gray-400' }} transition">
                                    {{ $lesson->title }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($enrollment)
                                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1 group-hover:underline">
                                        Ir a lección
                                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-3.5 w-3.5" />
                                    </span>
                                @else
                                    {{-- Etiqueta de candado más clara para usuarios no inscritos --}}
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-m-lock-closed" class="h-3.5 w-3.5" />
                                        Bloqueado
                                    </span>
                                @endif
                            </div>

                        @if ($lessonUrl)
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <div class="px-6 py-4 text-sm text-gray-400 italic text-center">
                            Sin lecciones activas en este módulo.
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
    @empty
        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 rounded-xl border border-dashed border-gray-300 dark:border-white/10">
            Este curso aún no tiene módulos asignados.
        </div>
    @endforelse
</div>