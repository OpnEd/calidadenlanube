<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Sección de Objetivos de Calidad --}}
        @if (!empty($policyData['objectives']))
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-rocket-launch"
                            class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                        <span class="text-xl font-semibold">Objetivos de Calidad</span>
                    </div>
                </x-slot>

                <div class="space-y-6">
                    @foreach ($policyData['objectives'] as $process => $objectives)
                        <div class="p-4 rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-tag"
                                    class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                {{ __(ucfirst($process)) }}
                            </h3>
                            @if (is_array($objectives))
                                <ul
                                    class="mt-2 list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-300 pl-4">
                                    @foreach ($objectives as $text)
                                        <li>{{ trim($text) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-gray-600 dark:text-gray-300 pl-4">{{ trim($objectives) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Sección de Compromisos --}}
        @if (!empty($policyData['commitments']))
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-shield-check"
                            class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                        <span class="text-xl font-semibold">Nuestros Compromisos</span>
                    </div>
                </x-slot>
                <ul class="mt-2 list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-300 pl-4">
                    @foreach ($policyData['commitments'] as $commitment)
                        <div class="p-4 rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                            <li>
                                {{ trim($commitment) }}
                            </li>
                        </div>
                    @endforeach
                </ul>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
