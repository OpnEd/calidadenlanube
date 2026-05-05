<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\Pages;

use App\Filament\Resources\Quality\Documentation\DocumentResource;
use App\Models\Document;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;
        $data['body'] = $this->buildBodyFromSections($data);

        $draft = new Document($data);
        $data['code'] = $draft->generated_code;

        if (!$draft->codeMatchesCategoryFormat($data['code'])) {
            throw ValidationException::withMessages([
                'document_category_id' => $draft->code_validation_message,
            ]);
        }

        $data['slug'] = Str::slug(($data['code'] ?? 'DOC') . '-' . ($data['title'] ?? 'documento') . '-' . now()->format('YmdHis'));

        return $data;
    }

    private function buildBodyFromSections(array $data): string
    {
        $sections = [
            'objectives' => collect(data_get($data, 'data.objectives', []))
                ->pluck('objective')
                ->filter()
                ->values()
                ->all(),
            'general_conditions' => data_get($data, 'data.general_conditions'),
            'definitions' => data_get($data, 'data.definitions', []),
            'normative_references' => data_get($data, 'data.normative_references', []),
            'procedure' => data_get($data, 'data.procedure', []),
        ];

        return json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
