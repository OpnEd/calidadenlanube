<?php

namespace App\Filament\Resources\Quality\Improvement;

use App\Filament\Resources\Quality\Improvement\PlanResource\Pages;
use App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers\ActionsRelationManager;
use App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers\EvidencesRelationManager;
use App\Filament\Resources\Quality\Improvement\PlanResource\RelationManagers\VerificationsRelationManager;
use App\Models\Quality\Improvement\Plan;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Planes de Mejora';
    protected static ?string $modelLabel = 'Plan de mejora';
    protected static ?string $pluralModelLabel = 'Planes de mejora';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Codigo')
                    ->maxLength(50),
                Forms\Components\Select::make('finding_id')
                    ->label('Hallazgo')
                    ->relationship('finding', 'title')
                    ->searchable()
                    ->preload(),
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
                Forms\Components\Select::make('owner_user_id')
                    ->label('Responsable')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                    ])
                    ->required()
                    ->default('media'),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'activo' => 'Activo',
                        'en_verificacion' => 'En verificacion',
                        'cerrado' => 'Cerrado',
                        'reabierto' => 'Reabierto',
                        'cancelado' => 'Cancelado',
                    ])
                    ->required()
                    ->default('borrador'),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha inicio'),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Fecha compromiso'),
                Forms\Components\TextInput::make('baseline_value')
                    ->label('Linea base')
                    ->numeric(),
                Forms\Components\TextInput::make('target_value')
                    ->label('Meta')
                    ->numeric(),
                Forms\Components\FileUpload::make('evidence_files')
                    ->label('Evidencias')
                    ->multiple()
                    ->disk('public')
                    ->directory('quality/improvement/plans')
                    ->downloadable()
                    ->openable(),
                Forms\Components\Textarea::make('objective')
                    ->label('Objetivo SMART')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('scope')
                    ->label('Alcance')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('expected_impact')
                    ->label('Impacto esperado')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('objective')
                    ->label('Objetivo')
                    ->limit(70)
                    ->searchable(),
                Tables\Columns\TextColumn::make('process.name')
                    ->label('Proceso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Responsable')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Compromiso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }

    public static function getRelations(): array
    {
        return [
            ActionsRelationManager::class,
            VerificationsRelationManager::class,
            EvidencesRelationManager::class,
        ];
    }
}
