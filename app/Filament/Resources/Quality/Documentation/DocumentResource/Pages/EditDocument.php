<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\Pages;

use App\Filament\Resources\Quality\Documentation\DocumentResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $sections = data_get($data, 'data', []);

        if (is_array($sections) && (
            isset($sections['objectives']) ||
            isset($sections['general_conditions']) ||
            isset($sections['definitions']) ||
            isset($sections['normative_references']) ||
            isset($sections['procedure'])
        )) {
            return $data;
        }

        $decodedBody = json_decode((string) ($data['body'] ?? ''), true);

        if (is_array($decodedBody)) {
            $data['data'] = array_merge($sections, [
                'objectives' => collect($decodedBody['objectives'] ?? [])
                    ->map(fn ($objective) => ['objective' => $objective])
                    ->values()
                    ->all(),
                'general_conditions' => $decodedBody['general_conditions'] ?? null,
                'definitions' => $decodedBody['definitions'] ?? [],
                'normative_references' => $decodedBody['normative_references'] ?? [],
                'procedure' => $decodedBody['procedure'] ?? [],
            ]);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $code = $record->code;
        $title = $data['title'] ?? $record->title;

        $data['slug'] = Str::slug($code . '-' . $title . '-' . $record->id);
        $data['body'] = $this->buildBodyFromSections($data);

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
