<?php

namespace App\Traits\Filament\Training;

use App\Models\Quality\Training\Lesson;
use Filament\Facades\Filament;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait HasLessonFormAndTable
{
    public static function buildLessonForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('module_id')
                            ->label('Módulo')
                            ->relationship(
                                name: 'module',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn(Builder $query) => $query->whereHas(
                                    'course',
                                    fn(Builder $courseQuery) => $courseQuery->ownedByTeam(Filament::getTenant()?->id)
                                )
                            )
                            ->required(),
                        Forms\Components\TextInput::make('duration')
                            ->label('Duración (minutos)')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('order')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('completion_mode')
                            ->label('Modo de cierre')
                            ->options([
                                Lesson::COMPLETION_MODE_CONSUMPTION_ONLY => 'Solo consumo',
                                Lesson::COMPLETION_MODE_ASSESSMENT_REQUIRED => 'Requiere evaluación',
                            ])
                            ->default(Lesson::COMPLETION_MODE_ASSESSMENT_REQUIRED)
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->label('Activa')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Contenido Didáctico')
                    ->schema([
                        Forms\Components\RichEditor::make('introduction')
                            ->label('Introducción')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción corta')
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('objectives')
                            ->label('Objetivos de aprendizaje')
                            ->simple(
                                Forms\Components\TextInput::make('objective')->required()
                            )
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('content')
                            ->label('Bloques de contenido')
                            ->simple(
                                Forms\Components\RichEditor::make('content_item')->required()
                            )
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('conclusions')
                            ->label('Conclusiones')
                            ->simple(
                                Forms\Components\TextInput::make('conclusion')->required()
                            )
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Multimedia y Referencias')
                    ->schema([
                        Forms\Components\FileUpload::make('ilustrations')
                            ->label('Ilustraciones / Imágenes')
                            ->multiple()
                            ->image()
                            ->directory('lessons/ilustrations'),
                        Forms\Components\TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('iframe')
                            ->label('Código Iframe')
                            ->maxLength(2000),
                        Forms\Components\Repeater::make('references')
                            ->label('Referencias y Bibliografía')
                            ->schema([
                                Forms\Components\TextInput::make('text')->label('Texto/Cita')->required(),
                                Forms\Components\TextInput::make('url')->label('Enlace (opcional)')->url(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function buildLessonTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
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
                Tables\Columns\TextColumn::make('duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('module.title')
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
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_mode')
                    ->label('Modo de cierre')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        Lesson::COMPLETION_MODE_CONSUMPTION_ONLY => 'Solo consumo',
                        Lesson::COMPLETION_MODE_ASSESSMENT_REQUIRED => 'Requiere evaluación',
                        default => $state ?? '-',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('video_url')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->relationship('module', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Módulo'),
                TernaryFilter::make('active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->actions([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
                    ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultGroup('module.title');
    }

    public static function buildLessonInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                Components\Section::make('Resumen de la Lección')
                    ->description('Información básica y objetivos de aprendizaje.')
                    ->schema([
                        Components\TextEntry::make('title')
                            ->label('Título')
                            ->weight('bold')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->columnSpanFull(),

                        Components\TextEntry::make('description')
                            ->label('Descripción')
                            ->markdown()
                            ->columnSpanFull(),

                        Components\RepeatableEntry::make('objectives')
                            ->label('Objetivos de Aprendizaje')
                            ->schema([
                                Components\TextEntry::make('')
                                    ->icon('heroicon-m-check')
                            ])
                            ->columnSpanFull(),

                        Components\TextEntry::make('duration')
                            ->label('Duración estimada')
                            ->suffix(' minutos')
                            ->icon('heroicon-m-clock'),

                        Components\TextEntry::make('order')
                            ->label('Posición en el módulo')
                            ->icon('heroicon-m-hashtag'),

                        Components\IconEntry::make('active')
                            ->label('Estado de publicación')
                            ->boolean(),

                        Components\TextEntry::make('completion_mode')
                            ->label('Modo de Finalización')
                            ->badge()
                            ->formatStateUsing(fn($state) => match ($state) {
                                Lesson::COMPLETION_MODE_CONSUMPTION_ONLY => 'Solo consumo',
                                Lesson::COMPLETION_MODE_ASSESSMENT_REQUIRED => 'Requiere evaluación',
                                default => $state,
                            }),
                    ])
            ]);
    }
}
