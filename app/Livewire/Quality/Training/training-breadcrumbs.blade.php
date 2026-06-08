@props(['breadcrumbs' => []])

<ol {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-sm']) }}>
    @foreach ($breadcrumbs as $crumb)
        <li class="flex items-center gap-2">
            @if (!$loop->first)
                <x-filament::icon icon="heroicon-m-chevron-right"
                    class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            @endif

            @if (!empty($crumb['url']))
                <a href="{{ $crumb['url'] }}"
                    class="inline-flex items-center rounded-full border border-transparent px-3 py-1.5 font-medium text-gray-600 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:text-gray-300 dark:hover:border-primary-500/30 dark:hover:bg-primary-500/10 dark:hover:text-primary-300">
                    {{ $crumb['label'] }}
                </a>
            @else
                <span class="inline-flex items-center rounded-full bg-primary-600 px-3 py-1.5 font-medium text-white">
                    {{ $crumb['label'] }}
                </span>
            @endif
        </li>
    @endforeach
</ol>
