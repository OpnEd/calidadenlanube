<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\ProductResource\Pages;
use App\Filament\TenantManager\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationIcon = 'phosphor-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship(name: 'product_category', titleAttribute: 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->required(),
                Forms\Components\Select::make('pharmaceutical_form_id')
                    ->relationship(name: 'pharmaceutical_form', titleAttribute: 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->default(null),
                Forms\Components\TextInput::make('bar_code')
                    ->placeholder('Código de barras')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->placeholder('Nombre comercial')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('drug')
                    ->placeholder('Principio activo')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('drug_concentration')
                    ->helperText('Cantidad de principio activo por unidad de medida (mg/ml, mg/tab, etc.)')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('recommended_dose')
                    ->placeholder('Dosis recomendada (por kg de peso)')
                    ->helperText('Dosis recomendada por kg de peso del paciente, medida en mg de principio activo (mg/kg/hr)')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->placeholder('Presentación comercial - concentración')
                    ->maxLength(255),
                Forms\Components\Toggle::make('fractionable')
                    ->label('Fraccionable')
                    ->live()
                    ->inline(false)
                    ->required(),
                Forms\Components\TextInput::make('conversion_factor')
                    ->label('Factor de conversion')
                    ->helperText('Numero de unidades minimas almacenables por una unidad comercial.')
                    ->hidden(fn(Get $get): bool => ! $get('fractionable'))
                    ->numeric()
                    ->required(fn(Get $get): bool => (bool) $get('fractionable'))
                    ->minValue(1)
                    ->step(1)
                    ->default(1),
                Forms\Components\TextInput::make('min_fraction')
                    ->label('Fraccion minima')
                    ->helperText('Cantidad minima consumible en la unidad de medida del producto. Ejemplo: 0.1 mL.')
                    ->hidden(fn(Get $get): bool => ! $get('fractionable'))
                    ->numeric()
                    ->required(fn(Get $get): bool => (bool) $get('fractionable'))
                    ->minValue(0.0001)
                    ->step(0.0001)
                    ->default(null),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\TextInput::make('tax')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_category.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pharmaceutical_form.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\IconColumn::make('fractionable')
                    ->label('Fraccionable')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('conversion_factor')
                    ->label('Factor conversion')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('min_fraction')
                    ->label('Fraccion minima')
                    ->numeric(decimalPlaces: 4)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('tax')
                    ->placeholder('IVA')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('status')
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
