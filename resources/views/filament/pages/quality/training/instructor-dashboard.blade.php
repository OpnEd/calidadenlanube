<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header con Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $stats = $this->getStatistics();
            @endphp
            
            <!-- Total Cursos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total de Cursos</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['total_courses'] }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ $stats['active_courses'] }} activos
                </p>
            </div>

            <!-- Total Estudiantes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Estudiantes Totales</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['total_students'] }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ $stats['students_in_progress'] }} en progreso
                </p>
            </div>

            <!-- Completados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Completados</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['students_completed'] }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ number_format($stats['completion_rate'], 1) }}% tasa
                </p>
            </div>

            <!-- Certificados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Certificados Emitidos</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['certificates_issued'] }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    Módulos: {{ $stats['total_modules'] }}
                </p>
            </div>
        </div>

        <!-- Cursos Impartidos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Cursos Impartidos
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Curso</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Estudiantes</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Completados</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Tasa Compl.</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Progreso Prom.</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Módulos</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->getCourseStats() as $courseStats)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ \App\Filament\Resources\Quality\Training\CourseResource::getUrl('edit', ['record' => $courseStats['id']]) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ $courseStats['title'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white font-semibold">
                                    {{ $courseStats['total_enrollments'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white font-semibold">
                                    {{ $courseStats['completed_enrollments'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($courseStats['completion_rate'] >= 80)
                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($courseStats['completion_rate'] >= 50)
                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else
                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @endif
                                    ">
                                        {{ number_format($courseStats['completion_rate'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full"
                                                 style="width: {{ $courseStats['average_progress'] }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($courseStats['average_progress'], 0) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white font-semibold">
                                    {{ $courseStats['modules_count'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($courseStats['active'])
                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else
                                            bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                        @endif
                                    ">
                                        @if($courseStats['active'])
                                            ✓ Activo
                                        @else
                                            Inactivo
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <p class="text-lg mb-2">📚 No tienes cursos asignados</p>
                                        <p class="text-sm">Contacta con un administrador para que te asigne cursos</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inscripciones Recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Inscripciones Recientes
                </h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->getRecentEnrollments() as $enrollment)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Estudiante y Cursor -->
                                <div class="flex items-center gap-3 mb-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $enrollment->user->name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $enrollment->user->email }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Curso -->
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Curso: <strong>{{ $enrollment->course->title }}</strong>
                                </p>

                                <!-- Status Badge -->
                                <span class="px-3 py-1 text-xs font-medium rounded-full mb-3 inline-block
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

                                <!-- Fechas y Certificado -->
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
                                    @if($enrollment->certificates->count() > 0)
                                        <span class="text-green-600 dark:text-green-400 font-semibold">
                                            🎓 Certificado emitido
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Botón de Acción -->
                            <a href="{{ \App\Filament\Resources\Quality\Training\EnrollmentResource::getUrl('view', ['record' => $enrollment->id]) }}"
                               class="ml-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm font-medium">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-gray-500 dark:text-gray-400">
                            <p class="text-lg mb-2">📭 Sin inscripciones recientes</p>
                            <p class="text-sm">Los estudiantes aparecerán aquí cuando se inscriban en tus cursos</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
