<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentVersionResource\Pages;

use App\Filament\Resources\Quality\Documentation\DocumentVersionResource;
use App\Models\Document;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentVersion extends CreateRecord
{
    protected static string $resource = DocumentVersionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncDocumentWithVersion();
    }

    private function syncDocumentWithVersion(): void
    {
        /** @var \App\Models\Quality\Documentation\DocumentVersion $version */
        $version = $this->record;

        if ($version->is_current) {
            $version->document->versions()
                ->whereKeyNot($version->id)
                ->update(['is_current' => false]);
        }

        Document::query()
            ->whereKey($version->document_id)
            ->update([
                'current_version_id' => $version->is_current ? $version->id : $version->document->current_version_id,
                'status' => $version->is_current ? $version->status : $version->document->status,
                'effective_at' => $version->is_current ? $version->effective_at : $version->document->effective_at,
                'expires_at' => $version->is_current ? $version->expires_at : $version->document->expires_at,
                'is_obsolete' => $version->is_current ? $version->status === 'obsolete' : $version->document->is_obsolete,
            ]);
    }
}

