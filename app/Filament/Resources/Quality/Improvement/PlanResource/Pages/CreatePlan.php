<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\Pages;

use App\Filament\Resources\Quality\Improvement\PlanResource;
use App\Models\Quality\Improvement\Evidence;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected array $evidenceFiles = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->evidenceFiles = $data['evidence_files'] ?? [];
        unset($data['evidence_files']);

        $data['team_id'] = Filament::getTenant()?->id;

        if (empty($data['code'])) {
            $data['code'] = 'PM-' . now()->format('Ymd-His');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $teamId = Filament::getTenant()?->id;

        foreach ($this->evidenceFiles as $filePath) {
            Evidence::create([
                'team_id' => $teamId,
                'plan_id' => $this->record->id,
                'uploaded_by' => auth()->id(),
                'file_path' => $filePath,
            ]);
        }
    }
}
