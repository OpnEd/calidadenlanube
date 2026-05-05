<?php

namespace App\Filament\Resources\Quality\Improvement;

use App\Filament\Resources\Quality\Improvement\ActionResource\Pages;
use App\Models\Quality\Improvement\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Acciones de Mejora';
    protected static ?string $modelLabel = 'Accion';
    protected static ?string $pluralModelLabel = 'Acciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_id')
                    ->label('Plan de mejora')
                    ->relationship(
                        name: 'plan',
                        titleAttribute: 'code',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when($tenantId, fn (Builder $q) => $q->where('team_id', $tenantId));
                        }
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => ($record->code ?: 'PM-' . $record->id) . ' | ' . str($record->objective)->limit(40)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
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
                    ->required()
                    ->default('pendiente'),
                Forms\Components\TextInput::make('progress')
                    ->label('% Avance')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha inicio'),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Fecha compromiso'),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Fecha cierre')
                    ->seconds(false),
                Forms\Components\TextInput::make('cost_estimated')
                    ->label('Costo estimado')
                    ->numeric(),
                Forms\Components\TextInput::make('cost_real')
                    ->label('Costo real')
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.code')
                    ->label('Plan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Avance')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Compromiso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
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
            'index' => Pages\ListActions::route('/'),
            'create' => Pages\CreateAction::route('/create'),
            'edit' => Pages\EditAction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }
}
