<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers;

use App\Models\Quality\Documentation\DocumentAcknowledgment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcknowledgmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'acknowledgments';

    protected static ?string $title = 'Acuses de lectura';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_version_id')
                    ->label('Versión')
                    ->relationship('version', 'version')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship(
                        name: 'user',
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
                    ->preload()
                    ->required(),
                Forms\Components\Toggle::make('required')
                    ->label('Obligatorio')
                    ->default(true),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'acknowledged' => 'Leído',
                        'overdue' => 'Vencido',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DatePicker::make('due_at')
                    ->label('Fecha límite'),
                Forms\Components\DateTimePicker::make('acknowledged_at')
                    ->label('Leído en')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version.version')
                    ->label('Versión')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\IconColumn::make('required')
                    ->label('Obligatorio')
                    ->boolean(),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Límite')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('acknowledged_at')
                    ->label('Leído en')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_acknowledged')
                    ->label('Marcar leído')
                    ->icon('heroicon-m-check')
                    ->action(function (DocumentAcknowledgment $record): void {
                        $record->update([
                            'status' => 'acknowledged',
                            'acknowledged_at' => now(),
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
