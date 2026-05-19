<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\CentralProductPriceResource\Pages;
use App\Models\CentralProductPrice;
use App\Models\Product;
use App\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CentralProductPriceResource extends Resource
{
    protected static ?string $model = CentralProductPrice::class;

    protected static ?string $navigationGroup = 'Gestión de productos';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Precios';
    protected static ?string $pluralModelLabel = 'Precios';
    protected static ?string $modelLabel = 'Precio';
    protected static ?string $slug = 'gestion-de-productos/precios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Codigo de producto (barras)')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'bar_code',
                        modifyQueryUsing: function (Builder $query, string $operation, ?CentralProductPrice $record): Builder {
                            $usedProductIds = CentralProductPrice::query()
                                ->when(
                                    $operation === 'edit' && filled($record),
                                    fn (Builder $priceQuery) => $priceQuery->whereKeyNot($record->getKey()),
                                )
                                ->select('product_id');

                            if ($operation === 'edit' && filled($record?->product_id)) {
                                return $query->where(function (Builder $productQuery) use ($record, $usedProductIds): Builder {
                                    return $productQuery
                                        ->whereKey($record->product_id)
                                        ->orWhereNotIn('products.id', $usedProductIds);
                                });
                            }

                            return $query->whereNotIn('products.id', $usedProductIds);
                        },
                    )
                    ->searchable(['bar_code', 'name'])
                    ->getOptionLabelFromRecordUsing(fn (Product $record): string => "{$record->bar_code} - {$record->name}")
                    ->placeholder('Seleccione el producto al que asignarás precio')
                    ->required()
                    ->preload(),
                Forms\Components\TextInput::make('min')
                    ->label('Stock mínimo')
                    ->required()
                    ->placeholder('Establece el stock mínimo para este producto')
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->placeholder('Establece el precio para este producto')
                    ->prefix('$'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.bar_code')
                    ->label('Codigo de producto (barras)')
                    ->searchable(['bar_code']),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Nombre del producto')
                    ->searchable(['name']),
                Tables\Columns\TextColumn::make('min')
                    ->label('Stock mínimo'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->successNotification(fn (): Notification => Notification::make()
                            ->color('success')
                            ->success()
                            ->title('Precio actualizado')
                            ->body(Str::markdown('El **precio** del producto fue actualizado correctamente.'))
                            ->icon('phosphor-tag')
                            ->iconColor('success')
                            ->size('4xl')),
                ]),
            ], position: ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListCentralProductPrices::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('product');
    }
}
