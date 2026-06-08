<?php

namespace App\Filament\Resources\Quality\Training\ModuleResource\RelationManagers;

use App\Filament\Resources\Quality\Training\LessonResource;
use App\Models\Quality\Training\Lesson;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Lecciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título de la Lección')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('objective')
                    ->label('Objetivo')
                    ->rows(2)
                    ->maxLength(500),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('content')
                    ->label('Contenido (HTML)')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('duration')
                    ->label('Duración (minutos)')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\TextInput::make('order')
                    ->label('Orden en el Módulo')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\TextInput::make('iframe')
                    ->label('Video Embed Code (iframe)')
                    ->maxLength(1000)
                    ->helperText('Pega el código iframe del video'),

                Forms\Components\Select::make('completion_mode')
                    ->label('Modo de Finalización')
                    ->options([
                        'consumption_only' => 'Solo consumo del material',
                        'assessment_required' => 'Requiere evaluación',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('active', true))
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_mode')
                    ->label('Finalización')
                    ->badge()
                    ->colors([
                        'info' => 'consumption_only',
                        'warning' => 'assessment_required',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'consumption_only' => 'Consumo',
                        'assessment_required' => 'Evaluación',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Activo'),

                Tables\Filters\SelectFilter::make('completion_mode')
                    ->label('Modo Finalización')
                    ->options([
                        'consumption_only' => 'Solo consumo',
                        'assessment_required' => 'Evaluación',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Lesson $record) => LessonResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => $this->ownerRecord->course?->team_id === Filament::getTenant()?->id),
                ]),
            ])
            ->defaultSort('order');
    }
}
