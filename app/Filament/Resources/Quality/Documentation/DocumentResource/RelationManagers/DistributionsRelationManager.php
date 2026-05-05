<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DistributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'distributions';

    protected static ?string $title = 'Distribución';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_version_id')
                    ->label('Versión')
                    ->relationship('version', 'version')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('scope_type')
                    ->label('Ámbito')
                    ->options([
                        'all' => 'Todos',
                        'process' => 'Proceso',
                        'role' => 'Rol',
                        'user' => 'Usuario',
                    ])
                    ->default('all')
                    ->required(),
                Forms\Components\TextInput::make('scope_id')
                    ->label('ID ámbito')
                    ->numeric(),
                Forms\Components\Toggle::make('required_read')
                    ->label('Lectura obligatoria')
                    ->default(true),
                Forms\Components\DateTimePicker::make('distributed_at')
                    ->label('Fecha distribución')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version.version')
                    ->label('Versión')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('scope_type')
                    ->label('Ámbito')
                    ->badge(),
                Tables\Columns\TextColumn::make('scope_id')
                    ->label('ID'),
                Tables\Columns\IconColumn::make('required_read')
                    ->label('Lectura obligatoria')
                    ->boolean(),
                Tables\Columns\TextColumn::make('distributor.name')
                    ->label('Distribuido por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('distributed_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['distributed_by'] = auth()->id();
                        $data['distributed_at'] = $data['distributed_at'] ?? now();
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

