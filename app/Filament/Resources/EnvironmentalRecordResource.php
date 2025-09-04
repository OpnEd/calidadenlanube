<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalRecordResource\Pages;
use App\Filament\Resources\EnvironmentalRecordResource\RelationManagers;
use App\Models\EnvironmentalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EnvironmentalRecordResource\Widgets\HumChart;
use App\Filament\Resources\EnvironmentalRecordResource\Widgets\TempChart;
use Filament\Resources\Resource;

class EnvironmentalRecordResource extends Resource
{
    protected static ?string $model = EnvironmentalRecord::class;
    
    protected static ?string $navigationGroup = 'Registros Ambientales';
    protected static ?string $navigationLabel = 'Temperatura y Humedad';
    //protected static ?string $navigationIcon = 'phosphor-thermometer-hot';
    protected static ?string $recordTitleAttribute = 'temp';
    protected static ?string $slug = 'variables-ambientales';

    /* public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->temp;
    } */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('temp')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('hum')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hum')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateEnvironmentalRecord::route('/create'),
            'edit' => Pages\EditEnvironmentalRecord::route('/{record}/edit'),
        ];
    }

    

    public static function getWidgets(): array
    {
        return [
            TempChart::class,
            HumChart::class
        ];
    }
}
