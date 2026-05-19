<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\EnvironmentalRecordResource\Pages;
use App\Models\EnvironmentalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnvironmentalRecordResource extends Resource
{
    protected static ?string $model = EnvironmentalRecord::class;

    protected static ?string $navigationGroup = 'Registros diarios';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Registros ambientales';
    protected static ?string $pluralModelLabel = 'Registros ambientales';
    protected static ?string $modelLabel = 'Registro ambiental';
    protected static ?string $slug = 'registros-diarios/variables-ambientales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('temp')
                    ->label('Temperatura (°C)')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('hum')
                    ->label('Humedad (%)')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp')
                    ->label('Temperatura (°C)')
                    ->numeric()
                    ->suffix(' °C')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hum')
                    ->label('Humedad (%)')
                    ->numeric()
                    ->suffix(' %HR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                // Ninguna acción masiva por ahora
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvironmentalRecords::route('/'),
            //'create' => Pages\CreateEnvironmentalRecord::route('/create'),
            //'edit' => Pages\EditEnvironmentalRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('team_id');
    }
}
