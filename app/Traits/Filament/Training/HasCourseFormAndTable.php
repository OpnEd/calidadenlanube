<?php

namespace App\Traits\Filament\Training;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

trait HasCourseFormAndTable
{
    public static function buildCourseForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medios')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen del Curso')
                            ->image()
                            ->disk('public')
                            ->directory('course_images')
                            ->maxSize(5120)
                            ->helperText('Máximo 5 MB. Formatos: JPG, PNG, GIF'),
                    ]),

                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título del Curso')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('objective')
                            ->label('Objetivo')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Detalles del Curso')
                    ->schema([
                        Forms\Components\Select::make('instructor_id')
                            ->label('Instructor')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Duración (horas)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Curso')
                            ->options([
                                'synchronous' => 'Sincrónico (en vivo)',
                                'asynchronous' => 'Asincrónico (a tu ritmo)',
                                'hybrid' => 'Híbrido',
                            ])
                            ->required(),

                        Forms\Components\Select::make('level')
                            ->label('Nivel')
                            ->options([
                                'beginner' => 'Principiante',
                                'intermediate' => 'Intermedio',
                                'advanced' => 'Avanzado',
                                'expert' => 'Experto',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('category')
                            ->label('Categoría')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio ($)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
                    ])->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Desactiva este curso para ocultarlo de los estudiantes'),
                    ]),
            ]);
    }

    public static function buildCourseTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular()
                    ->disk('public')
                    ->getStateUsing(fn ($record) => $record->image 
                        ? (str_starts_with($record->image, 'course_images/') ? $record->image : "course_images/{$record->image}") 
                        : null
                    ),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
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
                    }),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->label('Nivel')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'beginner' => 'Principiante',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado',
                        'expert' => 'Experto',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'synchronous' => 'Sincrónico',
                        'asynchronous' => 'Asincrónico',
                        'hybrid' => 'Híbrido',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->suffix(' h')
                    ->numeric(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Inscritos')
                    ->counts('enrollments')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Origen')
                    ->getStateUsing(fn($record) => $record->team_id === null ? 'Global' : 'Propio')
                    ->badge()
                    ->colors([
                        'success' => 'Global',
                        'info' => 'Propio',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Nivel')
                    ->options([
                        'beginner' => 'Principiante',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado',
                        'expert' => 'Experto',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'synchronous' => 'Sincrónico',
                        'asynchronous' => 'Asincrónico',
                        'hybrid' => 'Híbrido',
                    ]),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Activo'),

                Tables\Filters\SelectFilter::make('instructor_id')
                    ->label('Instructor')
                    ->relationship('instructor', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('enroll')
                        ->label('Inscribirme')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->hidden(
                            fn($record) =>
                            // Ocultar si ya existe una inscripción del usuario en este curso
                            Enrollment::where('course_id', $record->id)
                                ->where('user_id', auth()->id())
                                ->exists()
                        )
                        ->action(
                            fn($record) =>
                            Enrollment::create([
                                'course_id' => $record->id,
                                'user_id'   => auth()->id(),
                                'team_id'   => Filament::getTenant()?->id, // opcional
                                'status'    => 'in_progress',
                                'started_at' => now(),
                            ])
                        ),

                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => $record->team_id !== null),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->team_id !== null),
                    Tables\Actions\ViewAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-m-check')
                        ->action(function (Collection $records) {
                            $records->each(fn(Course $record) => $record->update(['active' => true]));
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-m-x-mark')
                        ->action(function (Collection $records) {
                            $records->each(fn(Course $record) => $record->update(['active' => false]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function buildCourseInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Grid::make(1)
                        ->schema([
                            Section::make('Resumen del Curso')
                                ->schema([
                                    TextEntry::make('title')
                                        ->label('Título')
                                        ->size(Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold'),

                                    TextEntry::make('objective')
                                        ->label('Objetivo Principal')
                                        ->markdown(),

                                    TextEntry::make('description')
                                        ->label('Descripción Detallada')
                                        ->markdown(),
                                ]),
                        ])->columnSpan(2),

                    Components\Group::make([
                        Components\Section::make()
                            ->schema([
                                Components\ImageEntry::make('image')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->height(200)
                                    ->width('100%')
                                    ->extraImgAttributes([
                                        'class' => 'rounded-lg object-cover w-full',
                                    ]),

                                Components\TextEntry::make('instructor.name')
                                    ->label('Instructor')
                                    ->icon('heroicon-m-user'),

                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('duration')
                                            ->label('Duración')
                                            ->suffix(' horas'),

                                        Components\TextEntry::make('level')
                                            ->label('Nivel')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'beginner' => 'Principiante',
                                                'intermediate' => 'Intermedio',
                                                'advanced' => 'Avanzado',
                                                'expert' => 'Experto',
                                                default => $state,
                                            })
                                            ->badge()
                                            ->color('info'),
                                    ]),

                                Components\TextEntry::make('type')
                                    ->label('Modalidad')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'synchronous' => 'Sincrónico',
                                        'asynchronous' => 'Asincrónico',
                                        'hybrid' => 'Híbrido',
                                        default => $state,
                                    }),

                                Components\TextEntry::make('price')
                                    ->label('Inversión')
                                    ->money('USD'),

                                Components\TextEntry::make('enrollments_count')
                                    ->label('Estudiantes inscritos')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        Components\Section::make('Estado y Origen')
                            ->schema([
                                Components\IconEntry::make('active')
                                    ->label('Estado de visibilidad')
                                    ->boolean(),

                                Components\TextEntry::make('team_id')
                                    ->label('Tipo de Recurso')
                                    ->getStateUsing(fn($record) => $record->team_id === null ? 'Global' : 'Propio')
                                    ->badge()
                                    ->color(fn($state) => $state === 'Global' ? 'success' : 'info'),
                            ]),
                    ])
                    ->columnSpan(1)
                    ->grow(false),
                ])->from('lg'),
            ])->columns(1);
    }
}
