<?php

namespace App\Traits\Filament\Training;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait HasModuleFormAndTable
{
    public static function buildModuleForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->label('Curso')
                            ->relationship(
                                name: 'course',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn(Builder $query) => $query->ownedByTeam(Filament::getTenant()?->id)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título del Módulo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('objective')
                            ->label('Objetivo')
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Orden dentro del Curso')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Duración (minutos)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen del Módulo')
                            ->image()
                            ->disk('public')
                            ->directory('module_images')
                            ->maxSize(5120),
                    ])->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function buildModuleTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(30)
                    ->sortable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Curso')
                    ->searchable()
                    ->limit(30)
                    ->sortable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->formatStateUsing(fn($state) => $state ? gmdate('H:i', $state * 60) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Lecciones'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Curso')
                    ->relationship('course', 'title')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('course_id', 'order')
            ->defaultGroup('course.title');
    }

    public static function buildModuleInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Split::make([
                    Components\Grid::make(1)
                        ->schema([
                            Components\Section::make('Detalles del Contenido')
                                ->schema([
                                    Components\TextEntry::make('title')
                                        ->label('Título del Módulo')
                                        ->size(Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold'),

                                    Components\TextEntry::make('objective')
                                        ->label('Objetivo de Aprendizaje')
                                        ->markdown(),

                                    Components\TextEntry::make('description')
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
                                    ->height(180)
                                    ->width('100%')
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl object-cover w-full shadow-sm',
                                    ]),

                                Components\TextEntry::make('course.title')
                                    ->label('Curso')
                                    ->icon('heroicon-m-academic-cap')
                                    ->color('primary')
                                    ->weight('semibold'),

                                Components\TextEntry::make('order')
                                    ->label('Orden en la secuencia')
                                    ->icon('heroicon-m-hashtag'),

                                Components\TextEntry::make('duration')
                                    ->label('Duración')
                                    ->formatStateUsing(fn($state) => $state ? gmdate('H:i', $state * 60) . ' h' : '-')
                                    ->icon('heroicon-m-clock'),

                                Components\IconEntry::make('active')
                                    ->label('Módulo activo')
                                    ->boolean(),
                            ]),
                    ])->columnSpan(1)->grow(false),
                ])->from('lg'),
            ])->columns(1);
    }
}
