<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers;

use App\Models\Quality\Documentation\DocumentApproval;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $title = 'Aprobaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_version_id')
                    ->label('Versión')
                    ->relationship('version', 'version')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('approver_id')
                    ->label('Aprobador')
                    ->relationship(
                        name: 'approver',
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
                    ->label('Decisión')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('approved_at')
                    ->label('Fecha aprobación')
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
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Aprobador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Decisión')
                    ->badge(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['approver_id'] = $data['approver_id'] ?? auth()->id();
                        $data['approved_at'] = $data['approved_at'] ?? now();
                        return $data;
                    })
                    ->after(function (DocumentApproval $record): void {
                        $this->syncDocumentState($record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DocumentApproval $record): void {
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

    protected function syncDocumentState(DocumentApproval $approval): void
    {
        if ($approval->status === 'approved') {
            $approval->document->update(['status' => 'approved']);
            $approval->version?->update(['approved_at' => $approval->approved_at ?? now(), 'status' => 'approved']);
        }

        if ($approval->status === 'rejected') {
            $approval->document->update(['status' => 'draft']);
            $approval->version?->update(['status' => 'draft']);
        }
    }
}
