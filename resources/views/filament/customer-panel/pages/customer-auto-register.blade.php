<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="store">
            <div class="mb-6">{{ $this->form }}</div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
