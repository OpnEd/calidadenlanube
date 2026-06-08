<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\AssessmentResource\Pages;
use App\Models\Quality\Training\Assessment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Evaluaciones';
    protected static ?string $pluralModelLabel = 'Evaluaciones';
    protected static ?string $modelLabel = 'Evaluación';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título de la Evaluación')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción / Instrucciones')
                            ->rows(3)
                            ->maxLength(655),
                        Forms\Components\Select::make('lesson_id')
                            ->label('Lección')
                            ->relationship(
                                name: 'lesson',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'module.course',
                                    fn (Builder $courseQuery) => $courseQuery->ownedByTeam(Filament::getTenant()?->id)
                                )
                            )
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Evaluación')
                            ->options([
                                'quiz' => 'Quiz',
                                'exam' => 'Examen',
                                'task' => 'Tarea',
                                'assignment' => 'Asignación',
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('Configuración de Puntuación')
                    ->schema([
                        Forms\Components\TextInput::make('max_score')
                            ->label('Puntaje Máximo')
                            ->numeric()
                            ->required()
                            ->default(100),
                        Forms\Components\TextInput::make('passing_score')
                            ->label('Puntaje Mínimo para Aprobar')
                            ->numeric()
                            ->required()
                            ->helperText('Puntuación mínima requerida para aprobar'),
                    ]),

                Forms\Components\Section::make('Límites de Intentos y Tiempo')
                    ->schema([
                        Forms\Components\TextInput::make('max_attempts')
                            ->label('Máximo de Intentos')
                            ->numeric()
                            ->helperText('Dejar vacío o 0 para permitir intentos ilimitados')
                            ->minValue(1),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duración Máxima (minutos)')
                            ->numeric()
                            ->helperText('Dejar vacío para sin límite de tiempo')
                            ->minValue(1),
                        Forms\Components\Toggle::make('show_feedback')
                            ->label('Mostrar Retroalimentación')
                            ->helperText('Si está activado, mostrar respuestas correctas después de evaluar')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lección')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_score')
                    ->label('Puntaje Máx')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('passing_score')
                    ->label('Puntaje Min')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_attempts')
                    ->label('Max Intentos')
                    ->formatStateUsing(fn ($state) => $state ? $state : 'Ilimitados')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración (min)')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : 'Sin límite')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit' => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->ownedByTeam(Filament::getTenant()?->id)
            ->with(['course', 'module', 'lesson']);
    }
}
