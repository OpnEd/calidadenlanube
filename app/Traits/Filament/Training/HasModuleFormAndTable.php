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
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;

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
                                modifyQueryUsing: function (Builder $query) {
                                    $tenant = Filament::getTenant();

                                    if ($tenant) {
                                        // Tenant panel context: lock to the active team
                                        $query->ownedByTeam($tenant->id);
                                    } else {
                                        // Admin panel context: fetch global courses
                                        $query->whereNull('team_id');
                                    }
                                }
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
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([

                        // MAIN CONTENT AREA (Spans 2 columns on large screens)
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextEntry::make('title')
                                            ->hiddenLabel()
                                            ->size(TextEntry\TextEntrySize::Large)
                                            ->weight('bold')
                                            ->color('primary')
                                            // Makes the title act like a true H1 header
                                            ->extraAttributes(['class' => 'text-2xl md:text-3xl tracking-tight']),

                                        TextEntry::make('objective')
                                            ->label('Objetivo de Aprendizaje')
                                            ->icon('heroicon-o-sparkles')
                                            ->iconColor('warning')
                                            ->markdown()
                                            ->prose(), // Formats the markdown beautifully like an article

                                        TextEntry::make('description')
                                            ->label('Descripción Detallada')
                                            ->icon('heroicon-o-book-open')
                                            ->markdown()
                                            ->prose(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 2]),

                        // SIDEBAR / METADATA (Spans 1 column on large screens)
                        Group::make()
                            ->schema([
                                Section::make('Detalles')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        ImageEntry::make('image')
                                            ->hiddenLabel()
                                            ->disk('public')
                                            ->height(180)
                                            ->extraImgAttributes([
                                                'class' => 'rounded-xl object-cover w-full shadow-sm',
                                            ]),

                                        TextEntry::make('course.title')
                                            ->label('Curso')
                                            ->icon('heroicon-m-academic-cap')
                                            ->badge() // Badges make categorization instantly scannable
                                            ->color('info')
                                            ->tooltip(fn($state) => $state),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('duration')
                                                    ->label('Duración')
                                                    ->formatStateUsing(fn($state) => "{$state} min")
                                                    ->icon('heroicon-m-clock')
                                                    ->color('gray'),

                                                TextEntry::make('order')
                                                    ->label('Módulo N°')
                                                    ->icon('heroicon-m-list-bullet')
                                                    ->badge()
                                                    ->color('success'),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 1]),
                    ]),
            ]);
    }
}
