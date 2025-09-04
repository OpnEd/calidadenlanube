<div class="space-y-6">
                        
    {{-- Cuadrícula de cursos --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $course)
            {{-- Tarjeta de curso individual --}}
            <div
                class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                {{-- Imagen de la tarjeta --}}
                    <div
                        class="flex flex-col items-center justify-center p-12 text-center bg-gray-50 rounded-lg dark:bg-gray-800/50">
                        <x-filament::icon icon="heroicon-o-video-camera-slash"
                            class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    </div>
                {{-- <div class="flex-shrink-0">
                    @if ($course->image_url)
                        <img class="h-48 w-full object-cover" src="{{ $course->image_url }}"
                             alt="Imagen del curso {{ $course->title }}">
                    @else
                        <div class="flex h-48 w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                            <x-filament::icon icon="heroicon-o-photo" class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                        </div>
                    @endif
                </div> --}}

                {{-- Cuerpo de la tarjeta --}}
                <div class="flex flex-1 flex-col justify-between p-6">
                    <div class="flex-1">
                        <x-filament::link href="#" class="block mt-2">
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $course->title }}</p>
                        </x-filament::link>

                        @if ($course->objective)
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ \Illuminate\Support\Str::limit($course->objective, 100) }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-6">
                        <div class="flex items-center space-x-3">
                            @if ($course->instructor)
                                <x-filament::badge size="xs" color="gray" icon="heroicon-m-user-circle">
                                    {{ $course->instructor->name }}
                                </x-filament::badge>
                            @endif
                        </div>

                        <div>
                            @php
                                // seguridad: comprobar que userEnrollments es Collection
                                $hasEnrollments = $userEnrollments instanceof \Illuminate\Support\Collection;
                                $enrollment = $hasEnrollments ? $userEnrollments->get($course->id) : null;
                                // Si $enrollment es el modelo, sacamos su id; si userEnrollments fue pluck, get() puede devolver id directamente
                                $enrollmentId = $enrollment?->id ?? $enrollment;
                            @endphp

                            @if ($hasEnrollments && $enrollmentId)
                                {{-- Si el usuario está inscrito, mostrar botón de acción --}}
                                {{-- Si el usuario está inscrito, ir al Enrollment específico --}}
                                <x-filament::button wire:click="goToEnrollment({{ $enrollmentId }})"
                                    tag="button" color="success" size="xs">
                                    !Ya estás inscrito!
                                </x-filament::button>
                                
                            @else
                                {{-- Si no está inscrito, mostrar acción de inscripción --}}
                                <x-filament::button wire:click="confirmEnrollment({{ $course->id }})" color="primary">
                                    Inscribirse
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <h3>No hay cursos disponibles.</h3>
            </div>
        @endforelse
    </div>
</div>
