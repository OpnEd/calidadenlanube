<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers;

use App\Models\Quality\Documentation\DocumentReview;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Revisiones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_version_id')
                    ->label('Versión')
                    ->relationship('version', 'version')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('reviewer_id')
                    ->label('Revisor')
                    ->relationship(
                        name: 'reviewer',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when(
                                $tenantId,
                                fn (Builder $q) => $q->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->where('teams.id', $tenantId))
                            );
                        }
                    )
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->label('Resultado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('reviewed_at')
                    ->label('Fecha revisión')
                    ->seconds(false),
                Forms\Components\Textarea::make('comments')
                    ->label('Comentarios')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version.version')
                    ->label('Versión')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Revisor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Resultado')
                    ->badge(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['reviewer_id'] = $data['reviewer_id'] ?? auth()->id();
                        $data['reviewed_at'] = $data['reviewed_at'] ?? now();
                        return $data;
                    })
                    ->after(function (DocumentReview $record): void {
                        $this->syncDocumentState($record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DocumentReview $record): void {
                        $this->syncDocumentState($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function syncDocumentState(DocumentReview $review): void
    {
        if ($review->status === 'approved') {
            $review->document->update(['status' => 'in_review']);
            $review->version?->update(['reviewed_at' => $review->reviewed_at ?? now()]);
        }

        if ($review->status === 'rejected') {
            $review->document->update(['status' => 'draft']);
        }
    }
}
