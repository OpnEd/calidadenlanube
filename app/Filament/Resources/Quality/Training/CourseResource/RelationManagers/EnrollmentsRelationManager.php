<?php

namespace App\Filament\Resources\Quality\Training\CourseResource\RelationManagers;

use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Inscripciones';

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('team_id', Filament::getTenant()?->id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Estudiante')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'teams',
                            fn ($q) => $q->whereKey(Filament::getTenant()?->id)
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'not_started' => 'No iniciado',
                        'in_progress' => 'En progreso',
                        'completed' => 'Completado',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('progress')
                    ->label('Progreso (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Estudiante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'not_started',
                        'info' => 'in_progress',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'not_started' => 'No iniciado',
                        'in_progress' => 'En progreso',
                        'completed' => 'Completado',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progreso')
                    ->formatStateUsing(fn($state) => "{$state}%")
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Finalización')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('certificates.id')->boolean() // Esto le dice que el estado será true/false
                    ->getStateUsing(fn($record) => $record->certificates()->exists())
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle') // Opcional: icono si no existe
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'not_started' => 'No iniciado',
                        'in_progress' => 'En progreso',
                        'completed' => 'Completado',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = Filament::getTenant()?->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Marcar como completado')
                        ->icon('heroicon-m-check')
                        ->action(function (Collection $records) {
                            $records->each(function (Enrollment $record) {
                                $record->update([
                                    'status' => 'completed',
                                    'progress' => 100,
                                    'completed_at' => now(),
                                ]);
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
