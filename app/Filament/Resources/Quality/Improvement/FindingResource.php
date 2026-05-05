<?php

namespace App\Filament\Resources\Quality\Improvement;

use App\Filament\Resources\Quality\Improvement\FindingResource\Pages;
use App\Models\Quality\Improvement\Finding;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FindingResource extends Resource
{
    protected static ?string $model = Finding::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Hallazgos de Mejora';
    protected static ?string $modelLabel = 'Hallazgo';
    protected static ?string $pluralModelLabel = 'Hallazgos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('process_id')
                    ->label('Proceso')
                    ->relationship(
                        name: 'process',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when(
                                $tenantId,
                                fn (Builder $q) => $q->whereNull('team_id')->orWhere('team_id', $tenantId)
                            );
                        }
                    )
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('source')
                    ->label('Origen')
                    ->options([
                        'auditoria' => 'Auditoria',
                        'incidente' => 'Incidente',
                        'queja' => 'Queja',
                        'kpi' => 'Indicador',
                        'riesgo' => 'Riesgo',
                        'otro' => 'Otro',
                    ])
                    ->required()
                    ->default('otro'),
                Forms\Components\Select::make('severity')
                    ->label('Severidad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'critica' => 'Critica',
                    ])
                    ->required()
                    ->default('media'),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'en_analisis' => 'En analisis',
                        'planificado' => 'Planificado',
                        'cerrado' => 'Cerrado',
                    ])
                    ->required()
                    ->default('abierto'),
                Forms\Components\TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('detected_at')
                    ->label('Fecha de deteccion')
                    ->seconds(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('process.name')
                    ->label('Proceso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origen')
                    ->badge(),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Severidad')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('detected_at')
                    ->label('Detectado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListFindings::route('/'),
            'create' => Pages\CreateFinding::route('/create'),
            'edit' => Pages\EditFinding::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }
}
