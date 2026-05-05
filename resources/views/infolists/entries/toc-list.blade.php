<div class="space-y-1 text-sm">
    <ul class="list-none m-0 p-0">
        @foreach ($getState() as $item)
            @php
                [$ml, $textClasses] = match ($item['level']) {
                    1 => ['pl-0', 'text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400'],
                    2 => ['pl-2', 'text-sm font-semibold text-gray-800 dark:text-gray-100'],
                    3 => ['pl-4', 'text-xs text-gray-600 dark:text-gray-300'],
                    4 => ['pl-6', 'text-xs text-gray-500 dark:text-gray-400'],
                    default => ['pl-0', 'text-sm'],
                };
            @endphp

            <li class="flex items-start gap-1.5 {{ $ml }}">
                <span
                    class="mt-1 h-1.5 w-1.5 rounded-full
                    {{ $item['level'] <= 2 ? 'bg-primary-500' : 'bg-gray-400 dark:bg-gray-500' }}"
                ></span>

                <a
                    href="#{{ $item['id'] }}"
                    class="flex-1 block rounded px-1 py-0.5 hover:bg-primary-50 dark:hover:bg-gray-800"
                >
                    {{ $item['text'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
