<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\AssessmentResource\Pages;
use App\Filament\TenantManager\Resources\Training\AssessmentResource\RelationManagers;
use App\Models\Quality\Training\Assessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\ToggledFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;
    
    protected static ?string $navigationGroup = 'Capacitación';

    protected static ?string $modelLabel = 'Evaluación';
    
    protected static ?string $pluralModelLabel = 'Evaluaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Main Content Column
                Group::make()
                    ->schema([
                        Section::make('Información General')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título de la Evaluación')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('description')
                                    ->label('Instrucciones o Descripción')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Preguntas y Opciones')
                            ->description('Administre las preguntas de la evaluación y marque la opción correcta.')
                            ->schema([
                                Repeater::make('questions')
                                    ->relationship('questions') // Assumes hasMany relationship named 'questions'
                                    ->label('Preguntas')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'Nueva Pregunta')
                                    ->schema([
                                        TextInput::make('question_text')
                                            ->label('Texto de la Pregunta')
                                            ->required()
                                            ->columnSpanFull(),

                                        Repeater::make('options')
                                            ->relationship('questionOptions') // Assumes Question hasMany options relationship
                                            ->label('Opciones de Respuesta')
                                            ->grid(2)
                                            ->defaultItems(2)
                                            ->schema([
                                                TextInput::make('option_text')
                                                    ->label('Texto de la Opción')
                                                    ->required(),
                                                
                                                Toggle::make('is_correct')
                                                    ->label('¿Es correcta?')
                                                    ->inline(false),
                                            ]),
                                    ])
                                    ->addActionLabel('Agregar Pregunta'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Sidebar Configuration Column
                Group::make()
                    ->schema([
                        Section::make('Ubicación del Currículo')
                            ->schema([
                                Select::make('course_id')
                                    ->label('Curso')
                                    ->relationship('course', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('module_id', null) ?? $set('lesson_id', null)),

                                Select::make('module_id')
                                    ->label('Módulo')
                                    ->relationship(
                                        name: 'module',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query, Forms\Get $get) => $query->where('course_id', $get('course_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(fn (Forms\Get $get): bool => ! $get('course_id'))
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('lesson_id', null)),

                                Select::make('lesson_id')
                                    ->label('Lección')
                                    ->relationship(
                                        name: 'lesson',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query, Forms\Get $get) => $query->where('module_id', $get('module_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Forms\Get $get): bool => ! $get('module_id')),
                            ]),

                        Section::make('Configuración de la Evaluación')
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'quiz' => 'Quiz',
                                        'examen' => 'Examen',
                                        'tarea' => 'Tarea',
                                    ])
                                    ->required()
                                    ->default('quiz'),

                                TextInput::make('max_score')
                                    ->label('Puntaje Máximo')
                                    ->numeric()
                                    ->required()
                                    ->default(5.0),

                                TextInput::make('passing_score')
                                    ->label('Puntaje de Aprobación')
                                    ->numeric()
                                    ->required()
                                    ->default(3.0),

                                TextInput::make('max_attempts')
                                    ->label('Intentos Máximos')
                                    ->numeric()
                                    ->placeholder('Ilimitados')
                                    ->helperText('Dejar en blanco para intentos ilimitados.'),

                                TextInput::make('duration_minutes')
                                    ->label('Duración (Minutos)')
                                    ->numeric()
                                    ->placeholder('Sin límite')
                                    ->suffix('min')
                                    ->helperText('Dejar en blanco si no tiene tiempo límite.'),

                                Toggle::make('show_feedback')
                                    ->label('Mostrar Feedback')
                                    ->helperText('Mostrar respuestas correctas al finalizar.')
                                    ->default(true),

                                Toggle::make('active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('course.title')
                    ->label('Curso')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'examen' => 'danger',
                        'tarea' => 'warning',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('passing_score')
                    ->label('Aprobación')
                    ->description(fn (Assessment $record): string => "de {$record->max_score}")
                    ->alignCenter(),

                TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : 'Sin límite')
                    ->alignCenter(),

                IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'quiz' => 'Quiz',
                        'examen' => 'Examen',
                        'tarea' => 'Tarea',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit' => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }
}
