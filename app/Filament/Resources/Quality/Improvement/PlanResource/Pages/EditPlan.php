<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\Pages;

use App\Filament\Resources\Quality\Improvement\PlanResource;
use App\Models\Quality\Improvement\Evidence;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected array $evidenceFiles = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['evidence_files'] = $this->record
            ->evidences()
            ->pluck('file_path')
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->evidenceFiles = $data['evidence_files'] ?? [];
        unset($data['evidence_files']);

        return $data;
    }

    protected function afterSave(): void
    {
        $currentPaths = $this->record->evidences()->pluck('file_path')->all();
        $newPaths = $this->evidenceFiles;

        $pathsToDelete = array_diff($currentPaths, $newPaths);
        $pathsToAdd = array_diff($newPaths, $currentPaths);

        if (!empty($pathsToDelete)) {
            Evidence::query()
                ->where('plan_id', $this->record->id)
                ->whereIn('file_path', $pathsToDelete)
                ->delete();
        }

        foreach ($pathsToAdd as $filePath) {
            Evidence::create([
                'team_id' => $this->record->team_id,
                'plan_id' => $this->record->id,
                'uploaded_by' => auth()->id(),
                'file_path' => $filePath,
            ]);
        }
    }
}
