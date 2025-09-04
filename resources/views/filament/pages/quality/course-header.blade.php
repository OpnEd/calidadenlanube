<x-filament::section>
{{dd($modules)}}
    <x-slot name="heading">
        <div class="text-2xl font-semibold mb-4">
            <h1>{{ $course->title }}</h1>
        </div>
    </x-slot>
    <div class="flex flex-col space-y-2 relative overflow-hidden"
        style="
        background-image: url('course-images/'.$course->image');
        background-size: cover;
        background-position: center;
    ">
        {{-- Capa semitransparente negra sobre la imagen --}}
        <div class="absolute inset-0 bg-black opacity-30"></div>

        {{-- Contenido encima --}}
        <div class="relative z-10 p-4">
            @if (!empty($course->description))
                <div>{{ $course->description }}</div>
            @endif
            @if (count($headerLinks))
                <div class="flex items-center space-x-2 gap-2">
                    @foreach ($headerLinks as $link)
                        <x-filament::link :color="$link['color']" :icon="$link['icon']" :href="route($link['route'], $link['params'] ?? [])" target="_blank">
                            {{ $link['label'] }}
                        </x-filament::link>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament::section>
