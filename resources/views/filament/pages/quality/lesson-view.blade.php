<x-filament-panels::page>
    <livewire:quality.training.lesson-component 
        :enrollment="$this->enrollment" 
        :lesson="$this->getLesson()" 
    />
</x-filament-panels::page>
