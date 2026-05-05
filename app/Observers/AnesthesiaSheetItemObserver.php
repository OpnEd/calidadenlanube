<?php

namespace App\Observers;

use App\Models\AnesthesiaSheetItem;
use App\Models\AnesthesiaSheetItemVersion;
use App\Models\ModelVersion;
use Illuminate\Support\Facades\Auth;
use KardexService;

class AnesthesiaSheetItemObserver
{
    public function updating($model)
    {
        $dirty = $model->getDirty();           // campos modificados
        $original = $model->getOriginal();     // valores antiguos

        $changes = [];

        foreach ($dirty as $field => $new) {
            // opcional: omitir campos irrelevantes como timestamps
            if (in_array($field, ['updated_at'])) {
                continue;
            }
            $changes[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $new,
            ];
        }

        if (!empty($changes)) {
            ModelVersion::create([
                'team_id'           => $model->team_id,
                'versionable_type'  => $model->getMorphClass(),
                'versionable_id'    => $model->id,
                'user_id'           => Auth::id(),
                'changes'           => $changes,
                'snapshot'          => $model->toArray(),
                'comment'           => $comment ?? null,
            ]);
        }
    }

    public function updated(AnesthesiaSheetItem $item)
    {
        $original = $item->getOriginal();

        $relevantFields = [
            'inventory_id',
            'dose_measure',
            'dose_measure_unit',
        ];

        $dirtyRelevant = collect($item->getDirty())
            ->only($relevantFields)
            ->toArray();

        if (!empty($dirtyRelevant)) {

            app(KardexService::class)
                ->handleAnesthesiaItemUpdate($item, $original);
        }
    }
}
