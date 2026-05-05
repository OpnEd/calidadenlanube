<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentVersionResource\Pages;

use App\Filament\Resources\Quality\Documentation\DocumentVersionResource;
use App\Models\Document;
use Filament\Resources\Pages\EditRecord;

class EditDocumentVersion extends EditRecord
{
    protected static string $resource = DocumentVersionResource::class;

    protected function afterSave(): void
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

