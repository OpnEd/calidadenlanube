<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header con Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $stats = $this->getStatistics();
            @endphp
            
            <!-- Total Inscripciones -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total de Cursos</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['total_enrollments'] }}
                </div>
            </div>

            <!-- En Progreso -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">En Progreso</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['in_progress'] }}
                </div>
            </div>

            <!-- Completados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Completados</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['completed'] }}
                </div>
            </div>

            <!-- Progreso Promedio -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Progreso Promedio</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ number_format($stats['average_progress'] ?? 0, 0) }}%
                </div>
            </div>
        </div>

        <!-- Cursos Inscritos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Mis Cursos
                </h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->getEnrollments() as $enrollment)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Título y Instructor -->
                                <div class="flex items-center gap-3 mb-3">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $enrollment->course->title }}
                                    </h3>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        @if($enrollment->status === 'completed')
                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($enrollment->status === 'in_progress')
                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else
                                            bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                        @endif
                                    ">
                                        @if($enrollment->status === 'completed')
                                            ✓ Completado
                                        @elseif($enrollment->status === 'in_progress')
                                            En Progreso
                                        @else
                                            No Iniciado
                                        @endif
                                    </span>
                                </div>

                                <!-- Instructor -->
                                @if($enrollment->course->instructor)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <strong>Instructor:</strong> {{ $enrollment->course->instructor->name }}
                                    </p>
                                @endif

                                <!-- Descripción -->
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                    {{ $enrollment->course->description }}
                                </p>

                                <!-- Barra de Progreso -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Progreso
                                        </span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $enrollment->progress }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                </div>

                                <!-- Fechas -->
                                <div class="flex gap-6 text-xs text-gray-500 dark:text-gray-400">
                                    @if($enrollment->started_at)
                                        <span>
                                            📅 Iniciado: {{ $enrollment->started_at->format('d/m/Y') }}
                                        </span>
                                    @endif
                                    @if($enrollment->completed_at)
                                        <span>
                                            ✓ Completado: {{ $enrollment->completed_at->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Botón de Acción -->
                            <a href="{{ \App\Filament\Resources\Quality\Training\EnrollmentResource::getUrl('view', ['record' => $enrollment->id]) }}"
                               class="ml-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                                @if($enrollment->status === 'completed')
                                    Ver Certificado
                                @else
                                    Continuar
                                @endif
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-gray-500 dark:text-gray-400">
                            <p class="text-lg mb-2">📚 No estás inscrito en ningún curso</p>
                            <p class="text-sm">Consulta con tu administrador sobre cursos disponibles</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
