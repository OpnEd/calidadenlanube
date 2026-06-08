<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Platform Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @php
                $stats = $this->getPlatformStats();
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total de Cursos</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['total_courses'] }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total de Inscripciones</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['total_enrollments'] }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Completados</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ $stats['completed_enrollments'] }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Tasa de Completación</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ number_format($stats['completion_rate'], 1) }}%
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <div class="text-sm text-gray-600 dark:text-gray-400">Progreso Promedio</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ number_format($stats['average_progress'], 1) }}%
                </div>
            </div>
        </div>

        <!-- Engagement Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $engagement = $this->getEngagementMetrics();
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Compromiso Última Semana</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Activos:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $engagement['active_last_week'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Tasa:</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($engagement['engagement_rate_week'], 1) }}%</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Compromiso Último Mes</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Activos:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $engagement['active_last_month'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Tasa:</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($engagement['engagement_rate_month'], 1) }}%</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Inactividad</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Inactivos (30d):</span>
                        <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ $engagement['inactive_count'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Distribución de Progreso</h2>
            <div class="space-y-4">
                @php
                    $progressDist = $this->getProgressDistribution();
                @endphp
                @forelse($progressDist as $dist)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24">
                            {{ $dist['label'] }}
                        </span>
                        <div class="flex-1 mx-4 bg-gray-200 dark:bg-gray-700 rounded-full h-8 flex items-center px-3">
                            <div class="bg-blue-500 h-full rounded-full flex items-center justify-center text-white text-xs font-bold"
                                 style="width: {{ $dist['count'] > 0 ? min($dist['count'] * 10, 100) : 5 }}%">
                                @if($dist['count'] > 0)
                                    {{ $dist['count'] }}
                                @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white w-12 text-right">
                            {{ $dist['count'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Sin datos</p>
                @endforelse
            </div>
        </div>

        <!-- Top Courses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Cursos Más Inscritos</h2>
                <div class="space-y-3">
                    @php
                        $topByEnrollment = $this->getTopCoursesbyEnrollment();
                    @endphp
                    @forelse($topByEnrollment as $course)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                {{ $course['title'] }}
                            </span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ $course['enrollment_count'] }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">Sin datos</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Cursos Más Completados</h2>
                <div class="space-y-3">
                    @php
                        $topByCompletion = $this->getTopCoursesByCompletion();
                    @endphp
                    @forelse($topByCompletion as $course)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                    {{ $course['title'] }}
                                </span>
                                <span class="text-sm font-bold
                                    @if($course['completion_rate'] >= 80)
                                        text-green-600 dark:text-green-400
                                    @elseif($course['completion_rate'] >= 50)
                                        text-yellow-600 dark:text-yellow-400
                                    @else
                                        text-red-600 dark:text-red-400
                                    @endif
                                ">
                                    {{ number_format($course['completion_rate'], 1) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full"
                                     style="width: {{ $course['completion_rate'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ $course['enrollments'] }} inscripciones
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">Sin datos</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Performance Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Desempeño de Cursos
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Curso</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Instructor</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Inscripciones</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Completadas</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Tasa</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Progreso</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Calif. Prom.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $performance = $this->getCoursePerformance();
                        @endphp
                        @forelse($performance as $course)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ \App\Filament\Resources\Quality\Training\CourseResource::getUrl('edit', ['record' => $course['id']]) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ $course['title'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600 dark:text-gray-400">
                                    {{ $course['instructor'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ $course['total_enrollments'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-green-600 dark:text-green-400">
                                    {{ $course['completed'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($course['completion_rate'] >= 80)
                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($course['completion_rate'] >= 50)
                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else
                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @endif
                                    ">
                                        {{ number_format($course['completion_rate'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 justify-center">
                                        <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full"
                                                 style="width: {{ $course['average_progress'] }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($course['average_progress'], 0) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ $course['average_score'] }}/100
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No hay cursos para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
