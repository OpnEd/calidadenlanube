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
                    ->default(fn () => Filament::getTenant()?->id)
                    ->hidden(),
                Forms\Components\Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'teams',
                            fn (Builder $teamQuery) => $teamQuery->whereKey(Filament::getTenant()?->id)
                        )
                    )
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->relationship(
                        name: 'course',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progreso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Iniciado el')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado el')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label('Último acceso')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificated_at')
                    ->label('Certificado el')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificate_url')
                    ->label('URL del certificado')
                    //->url()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('score_final')
                    ->label('Puntaje final')
                    ->numeric()
                    ->sortable(),
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
                    ->exporter(\App\Filament\Exporters\EnrollmentExporter::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
