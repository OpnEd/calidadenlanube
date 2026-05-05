<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Evidencias';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label('Archivo')
                    ->required()
                    ->disk('public')
                    ->directory('quality/improvement/plans')
                    ->downloadable()
                    ->openable(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Archivo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripcion')
                    ->limit(60),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Subido por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

