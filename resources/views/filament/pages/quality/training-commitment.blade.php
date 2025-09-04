<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Grid de acciones principales --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

            {{-- Card 1: Ver Cursos Disponibles --}}
            <x-filament::section class="flex flex-col justify-between">
                <div>
                    <span class="p-3 mb-4 inline-flex rounded-full bg-info-500/10 text-info-500 dark:bg-info-400/10 dark:text-info-400">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="w-6 h-6" />
                    </span>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        Cursos Disponibles
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Explora nuestro catálogo de cursos diseñados para potenciar tus habilidades y conocimientos.
                    </p>
                </div>
                <div class="mt-6">
                    <x-filament::button tag="a" :href="route('filament.admin.pages.course-list', ['tenant' => $this->teamId])" icon="heroicon-m-arrow-right"
                        icon-position="after" color="info" class="w-full">
                        Ir a Cursos
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- Card 2: Calendario de Capacitaciones --}}
            <x-filament::section class="flex flex-col justify-between">
                <div>
                    <span class="p-3 mb-4 inline-flex rounded-full bg-success-500/10 text-success-500 dark:bg-success-400/10 dark:text-success-400">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-6 h-6" />
                    </span>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        Calendario de Eventos
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Consulta las fechas de las próximas capacitaciones, talleres y eventos programados.
                    </p>
                </div>
                <div class="mt-6">
                    <x-filament::button tag="a" :href="route('filament.admin.pages.events', ['tenant' => $this->teamId])" icon="heroicon-m-arrow-right"
                        icon-position="after" color="success" class="w-full">
                        Ver Calendario
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- Card 3: Documentación y Procedimientos --}}
            <x-filament::section class="flex flex-col justify-between">
                <div>
                    <span class="p-3 mb-4 inline-flex rounded-full bg-warning-500/10 text-warning-500 dark:bg-warning-400/10 dark:text-warning-400">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-6 h-6" />
                    </span>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        Documentos de Referencia
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Accede al procedimiento de inducción y otros documentos clave para tu formación.
                    </p>
                </div>
                <div class="mt-6">
                    <x-filament::button tag="a" :href="route('document.details', [
                        'tenant' => $this->teamId,
                        'document' => 'procedimiento-induccion-capacitacion',
                    ])" target="_blank" rel="noopener noreferrer" icon="heroicon-m-arrow-right"
                        icon-position="after" color="warning" class="w-full">
                        Ver Procedimiento
                    </x-filament::button>
                </div>
            </x-filament::section>

        </div>
    </div>
</x-filament-panels::page>
