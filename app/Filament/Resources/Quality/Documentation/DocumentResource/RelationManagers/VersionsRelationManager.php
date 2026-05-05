<?php

namespace App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers;

use App\Models\Quality\Documentation\DocumentVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Versiones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->label('Versión')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'in_review' => 'En revisión',
                        'approved' => 'Aprobado',
                        'published' => 'Publicado',
                        'obsolete' => 'Obsoleto',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Toggle::make('is_current')
                    ->label('Versión vigente'),
                Forms\Components\DatePicker::make('effective_at')
                    ->label('Vigente desde'),
                Forms\Components\DatePicker::make('expires_at')
                    ->label('Vigente hasta'),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Archivo')
                    ->disk('public')
                    ->directory('quality/documents/versions')
                    ->downloadable()
                    ->openable(),
                Forms\Components\Textarea::make('change_summary')
                    ->label('Resumen del cambio')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('body')
                    ->label('Contenido')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_current')
                    ->label('Vigente')
                    ->boolean(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = $this->ownerRecord->team_id;
                        $data['created_by'] = auth()->id();
                        return $data;
                    })
                    ->after(function (DocumentVersion $record): void {
                        $this->syncCurrentVersion($record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (DocumentVersion $record): void {
                        $this->syncCurrentVersion($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function syncCurrentVersion(DocumentVersion $version): void
    {
        if (!$version->is_current) {
            return;
        }

        $version->document->versions()
            ->whereKeyNot($version->id)
            ->update(['is_current' => false]);

        $version->document->update([
            'current_version_id' => $version->id,
            'status' => $version->status,
            'effective_at' => $version->effective_at,
            'expires_at' => $version->expires_at,
            'is_obsolete' => $version->status === 'obsolete',
        ]);
    }
}

