<x-filament::section :icon="$customPageIcon">
    {{-- Usamos el slot 'heading' para el título principal. Filament se encargará del estilo. --}}
    <x-slot name="heading">
        {{ $customTitle }}
    </x-slot>

    {{-- Usamos el slot 'description' para el subtítulo. Es más semántico y visualmente consistente. --}}
    @if (!empty($customSubtitle))
        <x-slot name="description">
            {{ $customSubtitle }}
        </x-slot>
    @endif

    {{-- El contenido principal (los enlaces) va aquí. Eliminamos la duplicación. --}}
    @if (count($headerLinks))
        <div class="flex items-center gap-4">
            @foreach ($headerLinks as $link)
                <x-filament::link :color="$link['color'] ?? 'primary'" :icon="$link['icon']" :href="$link['url'] ?? route($link['route'], $link['params'] ?? [])"  target="_blank">
                    {{ $link['label'] }}
                </x-filament::link>
            @endforeach
        </div>
    @endif
</x-filament::section>