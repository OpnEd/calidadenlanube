<?php

namespace App\Traits\Filament\Training;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait HasEnrollmentFormAndTable
{
    public static function buildEnrollmentForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('team_id')
                    ->default(fn() => Filament::getTenant()?->id)
                    ->hidden(),
                Forms\Components\Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->whereHas(
                            'teams',
                            fn(Builder $teamQuery) => $teamQuery->whereKey(Filament::getTenant()?->id)
                        )
                    )
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->relationship(
                        name: 'course',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->active()
                            ->ownedByTeam(Filament::getTenant()?->id)
                    )
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('in_progress'),
                Forms\Components\TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('started_at'),
                Forms\Components\DateTimePicker::make('completed_at'),
                Forms\Components\DateTimePicker::make('last_accessed_at'),
                Forms\Components\DateTimePicker::make('certificated_at'),
                Forms\Components\TextInput::make('certificate_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('score_final')
                    ->numeric(),
            ]);
    }

    public static function buildEnrollmentTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Curso')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->weight('bold') // Makes the primary identifier stand out
                    ->description(fn($record) => $record->started_at ? 'Inició: ' . $record->started_at->format('d/m/Y') : null),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()->formatStateUsing(fn(string $state): string => match (strtolower($state)) {
                        'completed', 'completado' => 'Completado',
                        'in_progress', 'en progreso' => 'En progreso',
                        'failed', 'reprobado' => 'Reprobado',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'completado', 'completed' => 'success',
                        'en progreso', 'in_progress' => 'warning',
                        'reprobado', 'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progreso')
                    ->numeric()
                    ->suffix('%') // Adds context to the number
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('score_final')
                    ->label('Puntaje')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label('Último acceso')
                    ->dateTime('d/m/Y g:i A') // Formats the date cleanly
                    ->sortable(),

                // --- Toggled Hidden Columns ---
                // These are available via the column visibility button but hide by default to prevent horizontal scrolling

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Iniciado el')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado el')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('certificated_at')
                    ->label('Certificado el')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray') // Adds a nice icon to the button
                    ->exporter(\App\Filament\Exporters\EnrollmentExporter::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    // Moved the certificate URL out of the columns and into a clean action
                    Tables\Actions\Action::make('certificate')
                        ->label('Ver Certificado')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->url(fn($record) => $record->certificate_url)
                        ->openUrlInNewTab()
                        ->visible(fn($record) => filled($record->certificate_url)),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
