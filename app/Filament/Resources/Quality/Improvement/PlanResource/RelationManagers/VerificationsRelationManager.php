<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers;

use App\Models\Quality\Improvement\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifications';

    protected static ?string $title = 'Verificaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('result')
                    ->label('Resultado')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'parcial' => 'Parcial',
                        'no_efectivo' => 'No efectivo',
                    ])
                    ->default('parcial')
                    ->required(),
                Forms\Components\TextInput::make('before_value')
                    ->label('Valor antes')
                    ->numeric(),
                Forms\Components\TextInput::make('after_value')
                    ->label('Valor despues')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Fecha verificacion')
                    ->seconds(false),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('result')
                    ->label('Resultado')
                    ->badge(),
                Tables\Columns\TextColumn::make('before_value')
                    ->label('Antes')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('after_value')
                    ->label('Despues')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verificado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['verified_by'] = auth()->id();
                        return $data;
                    })
                    ->after(function ($record): void {
                        $this->syncPlanStatusFromResult($record->result);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record): void {
                        $this->syncPlanStatusFromResult($record->result);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function syncPlanStatusFromResult(string $result): void
    {
        $status = match ($result) {
            'efectivo' => 'cerrado',
            'parcial' => 'en_verificacion',
            'no_efectivo' => 'reabierto',
            default => null,
        };

        if (!$status) {
            return;
        }

        Plan::query()
            ->whereKey($this->ownerRecord->id)
            ->update([
                'status' => $status,
                'closed_at' => $status === 'cerrado' ? now() : null,
            ]);
    }
}

