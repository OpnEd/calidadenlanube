<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\ProductReceptionResource\Pages;
use App\Filament\TenantManager\Resources\ProductReceptionResource\RelationManagers;
use App\Models\ProductReception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\Operations\HasInvoiceSelect;
use App\Enums\ProductReceptionStatus;
use Filament\Tables\Enums\ActionsPosition;

class ProductReceptionResource extends Resource
{
    use HasInvoiceSelect;

    protected static ?string $model = ProductReception::class;

    protected static ?string $navigationGroup = 'Operaciones internas';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Recepciones Técnicas';
    protected static ?string $pluralModelLabel = 'Recepciones Técnicas';
    protected static ?string $modelLabel = 'Recepción técnica';
    protected static ?string $slug = 'operaciones-internas/recepciones-tecnicas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('purchase_id')
                    ->label('Orden de compra')
                    ->relationship(
                        'purchase',
                        'code',
                        fn (Builder $query) => $query->whereNull('team_id')
                    )
                    ->helperText('Selecciona la orden de compra asociada a esta recepción técnica')
                    ->required(),

                static::invoiceSelect(),

                Forms\Components\Select::make('status')
                    ->label('Estado de la recepción')
                    ->options(ProductReceptionStatus::class)
                    ->required(),

                Forms\Components\DateTimePicker::make('reception_date')
                    ->label('Fecha de recepción')
                    ->required()
                    ->default(now()),

                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('data')
                    ->label('Datos adicionales')
                    ->keyPlaceHolder('Color')
                    ->valuePlaceHolder('rojo')
                    ->helperText('Puedes agregar cualquier información adicional relevante en formato JSON, por ejemplo: "color": "rojo", "tamaño": "grande"')
                    ->columnspanfull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.code')
                    ->label('Orden de compra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.code')
                    ->label('Factura')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->icon(fn(ProductReceptionStatus $state) => $state->getIcon())
                    ->tooltip(fn(ProductReceptionStatus $state) => $state->getLabel())
                    ->sortable(),
                Tables\Columns\TextColumn::make('reception_date')
                    ->label('Fecha de recepción')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Ninguna acción masiva por ahora
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
            'index' => Pages\ListProductReceptions::route('/'),
            'create' => Pages\CreateProductReception::route('/create'),
            'edit' => Pages\EditProductReception::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('team_id')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
