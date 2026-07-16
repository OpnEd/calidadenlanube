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
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Support\Enums\FontWeight;

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
                    ->getStateUsing(
                        fn($record) => $record->image
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('level')
                    ->label('Nivel')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'beginner' => 'Principiante',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado',
                        'expert' => 'Experto',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->suffix(' h')
                    ->numeric(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
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

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function buildCourseInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([

                        // COLUMNA PRINCIPAL: Contenido Académico (Ocupa 2 columnas en pantallas grandes)
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextEntry::make('title')
                                            ->hiddenLabel()
                                            ->size(TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->color('primary')
                                            // Transforma el título en un verdadero encabezado H1 con clases Tailwind
                                            ->extraAttributes(['class' => 'text-2xl md:text-3xl tracking-tight']),

                                        TextEntry::make('objective')
                                            ->label('Objetivo Principal')
                                            ->icon('heroicon-o-trophy')
                                            ->iconColor('warning')
                                            ->markdown()
                                            ->prose(), // Aplica espaciado tipográfico elegante a listas y textos

                                        TextEntry::make('description')
                                            ->label('Descripción Detallada')
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('primary')
                                            ->markdown()
                                            ->prose(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 2]),

                        // BARRA LATERAL: Metadatos e Información Comercial (Ocupa 1 columna)
                        Group::make()
                            ->schema([
                                Section::make('Ficha Técnica')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        ImageEntry::make('image')
                                            ->hiddenLabel()
                                            ->disk('public')
                                            ->height(180)
                                            ->extraImgAttributes([
                                                'class' => 'rounded-xl object-cover w-full shadow-sm mb-2',
                                            ]),

                                        TextEntry::make('instructor.name')
                                            ->label('Instructor')
                                            ->icon('heroicon-o-user-circle')
                                            ->weight(FontWeight::Medium),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('duration')
                                                    ->label('Duración')
                                                    ->icon('heroicon-o-clock')
                                                    ->suffix(' horas'),

                                                TextEntry::make('level')
                                                    ->label('Nivel')
                                                    ->badge()
                                                    // Semántica de colores según la complejidad del nivel
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'beginner' => 'success',
                                                        'intermediate' => 'warning',
                                                        'advanced', 'expert' => 'danger',
                                                        default => 'gray',
                                                    })
                                                    ->formatStateUsing(fn($state) => match ($state) {
                                                        'beginner' => 'Principiante',
                                                        'intermediate' => 'Intermedio',
                                                        'advanced' => 'Avanzado',
                                                        'expert' => 'Experto',
                                                        default => $state,
                                                    }),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('type')
                                                    ->label('Modalidad')
                                                    ->icon('heroicon-o-computer-desktop')
                                                    ->badge()
                                                    ->color('gray')
                                                    ->formatStateUsing(fn($state) => match ($state) {
                                                        'synchronous' => 'Sincrónico',
                                                        'asynchronous' => 'Asincrónico',
                                                        'hybrid' => 'Híbrido',
                                                        default => $state,
                                                    }),

                                                TextEntry::make('price')
                                                    ->label('Inversión')
                                                    ->money('USD')
                                                    ->weight(FontWeight::Bold)
                                                    ->color('primary'),
                                            ]),

                                        TextEntry::make('enrollments_count')
                                            ->label('Estudiantes Inscritos')
                                            ->icon('heroicon-o-users')
                                            ->badge()
                                            ->color('info'),
                                    ]),

                                // Sección compacta de control administrativo/SaaS
                                Section::make('Configuración y Visibilidad')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->compact()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                IconEntry::make('active')
                                                    ->label('Estado')
                                                    ->boolean(),

                                                TextEntry::make('team_id')
                                                    ->label('Ámbito')
                                                    ->getStateUsing(fn($record) => $record->team_id === null ? 'Global' : 'Propio')
                                                    ->badge()
                                                    ->color(fn($state) => $state === 'Global' ? 'success' : 'info'),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 1]),
                    ]),
            ]);
    }
}
