<?php

namespace App\Filament\Resources\Quality\Documentation;

use App\Filament\Resources\Quality\Documentation\ProcessResource\Pages;
use App\Models\Process;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProcessResource extends Resource
{
    protected static ?string $model = Process::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Procesos';
    protected static ?string $modelLabel = 'Proceso';
    protected static ?string $pluralModelLabel = 'Procesos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Codigo')
                    ->maxLength(50),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('process_type_id')
                    ->label('Tipo de proceso')
                    ->relationship('process_type', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del tipo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Codigo del tipo')
                            ->maxLength(50),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripcion')
                            ->rows(2),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('suppliers')
                    ->label('Proveedores'),
                Forms\Components\TagsInput::make('inputs')
                    ->label('Entradas'),
                Forms\Components\Repeater::make('procedures')
                    ->label('Procedimiento')
                    ->schema([
                        Forms\Components\TextInput::make('activity')
                            ->label('Actividad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('activity_description')
                            ->label('Descripcion')
                            ->rows(2)
                            ->required(),
                        Forms\Components\TextInput::make('responsible')
                            ->label('Responsable')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('records')
                            ->label('Registros')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('outputs')
                    ->label('Salidas'),
                Forms\Components\TagsInput::make('clients')
                    ->label('Clientes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('process_type.name')
                    ->label('Tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripcion')
                    ->limit(80),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcesses::route('/'),
            'create' => Pages\CreateProcess::route('/create'),
            'edit' => Pages\EditProcess::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when(
                $tenantId,
                fn (Builder $query) => $query->whereNull('team_id')->orWhere('team_id', $tenantId)
            );
    }
}

