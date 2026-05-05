<?php

namespace App\Filament\Resources\Quality\Improvement;

use App\Filament\Resources\Quality\Improvement\VerificationResource\Pages;
use App\Models\Quality\Improvement\Verification;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VerificationResource extends Resource
{
    protected static ?string $model = Verification::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Verificaciones de Eficacia';
    protected static ?string $modelLabel = 'Verificacion';
    protected static ?string $pluralModelLabel = 'Verificaciones';

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
                Forms\Components\Select::make('result')
                    ->label('Resultado')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'parcial' => 'Parcial',
                        'no_efectivo' => 'No efectivo',
                    ])
                    ->required()
                    ->default('parcial'),
                Forms\Components\TextInput::make('before_value')
                    ->label('Valor antes')
                    ->numeric(),
                Forms\Components\TextInput::make('after_value')
                    ->label('Valor despues')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Fecha verificacion')
                    ->seconds(false),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.code')
                    ->label('Plan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('result')
                    ->label('Resultado')
                    ->badge(),
                Tables\Columns\TextColumn::make('before_value')
                    ->label('Antes')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('after_value')
                    ->label('Despues')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verificado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Fecha')
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
            'index' => Pages\ListVerifications::route('/'),
            'create' => Pages\CreateVerification::route('/create'),
            'edit' => Pages\EditVerification::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }
}
