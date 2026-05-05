<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $title = 'Acciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
                Forms\Components\Select::make('responsible_user_id')
                    ->label('Responsable')
                    ->relationship('responsible', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En progreso',
                        'bloqueada' => 'Bloqueada',
                        'terminada' => 'Terminada',
                        'vencida' => 'Vencida',
                    ])
                    ->default('pendiente')
                    ->required(),
                Forms\Components\TextInput::make('progress')
                    ->label('% Avance')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha inicio'),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Fecha compromiso'),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Fecha cierre')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Avance')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Compromiso')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        return $data;
                    }),
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
}

