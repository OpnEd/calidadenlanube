<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';
    protected static ?string $title = 'Versiones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('changes')
                    ->label('Cambios')
                    ->formatStateUsing(fn($state): string => $this->toPrettyJson($state))
                    ->rows(12)
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('snapshot')
                    ->label('Snapshot')
                    ->formatStateUsing(fn($state): string => $this->toPrettyJson($state))
                    ->rows(12)
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('comment')
                    ->label('Comentario')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('changes')
                    ->label('Campos modificados')
                    ->getStateUsing(function ($record): string {
                        $changes = is_array($record->changes) ? $record->changes : [];

                        if (empty($changes)) {
                            return '-';
                        }

                        return collect(array_keys($changes))
                            ->map(fn(string $field): string => Str::headline($field))
                            ->implode(', ');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('clinic.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    protected function toPrettyJson(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                return $value;
            }
        }

        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? (string) $value : $json;
    }
}
