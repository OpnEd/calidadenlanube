<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetResource\Pages;
use App\Filament\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;
    
    protected static ?string $navigationGroup = 'Actores';
    protected static ?string $tenantOwnershipRelationshipName = 'customer';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'identification')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('species')
                    ->options([
                        'dog' => __('Dog'),
                        'cat' => __('Cat'),
                        'bird' => __('Bird'),
                        'reptile' => __('Reptile'),
                        'other' => __('Other'),
                    ])
                    ->required(),
                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => __('Male'),
                        'female' => __('Female'),
                        'other' => __('Other'),
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('birth_date')
                    ->required(),
                Forms\Components\TextInput::make('weight')
                    ->label(__('Weight (kg)'))
                    ->numeric(),
                Forms\Components\KeyValue::make('history')
                    ->keyLabel('Condition')
                    ->valueLabel('Details')
                    ->valuePlaceholder('e.g., Diagnosed with diabetes in 2020')
                    ->addActionLabel('Add Condition')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_alive')
                    ->label(__('Is Alive'))
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('species'),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_alive')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}


/* 

2) Alternativa (no tocar Team): ajustar el PetResource para scoping mediante whereHas / overriding getEloquentQuery

Si no quieres añadir la relación en Team, modifica el Resource de Pet para filtrar por team_id a través de customer:

Por qué usarlo:

No tocas el modelo Team.

Te da control fino sobre permisos y filtros por tenant.

Útil si la relación pets en Team no tiene sentido para otras partes de la app.

app/Filament/Resources/PetResource.php

use Illuminate\Database\Eloquent\Builder;

public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // Si hay un admin global, devolver todo:
    if (auth()->user()->hasRole('global-admin')) {
        return $query;
    }

    $teamId = auth()->user()->currentTeam->id ?? null;

    if ($teamId) {
        // Filtra pets cuyo customer pertenece al team
        return $query->whereHas('customer', function (Builder $q) use ($teamId) {
            $q->where('team_id', $teamId);
        });
    }

    // Por defecto, no devolver nada o devolver solo los no asignados (ajusta según política)
    return $query->whereRaw('1 = 0');
}
 */