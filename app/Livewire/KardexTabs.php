<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inventory;

class KardexTabs extends Component
{
    public $selected_medication = null;

    public function mount()
    {
        $this->selected_medication = 'todos';
    }

    public function selectMedication($medicationId)
    {
        $this->selected_medication = $medicationId === 'todos' ? null : $medicationId;
        $this->dispatch('medication-selected', $this->selected_medication);
    }

    public function render()
    {
        $medications = Inventory::whereHas('anesthesiaSheetItems')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return view('livewire.kardex-tabs', compact('medications'));
    }
}
