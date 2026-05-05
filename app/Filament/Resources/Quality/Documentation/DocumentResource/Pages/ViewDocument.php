<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\Pages;

use App\Filament\Resources\Quality\Documentation\DocumentResource;
use App\Filament\Resources\Quality\Documentation\DocumentVersionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Document;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (): bool => $this->record->status !== 'published'),
            Action::make('version')
                ->label('Versionar')
                ->icon('heroicon-m-document-duplicate')
                ->color('primary')
                ->url(fn (): string => DocumentVersionResource::getUrl('create') . '?document_id=' . $this->record->id)
                ->visible(fn (): bool => $this->record->status === 'published'),
            Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url(fn(Document $record) => route('documents.download', ['tenant' => Filament::getTenant()->id, 'document' => $record->slug]))
                ->openUrlInNewTab(),
        ];
    }
}
