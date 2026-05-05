<?php

namespace App\Filament\Resources\ProductReceptionResource\RelationManagers;

use App\Models\ProductReceptionItem;
use App\Services\ProductReceptionInventoryService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('batch_id')
                    ->relationship(
                        'batch',
                        'code',
                        fn (Builder $query) => $query->where('team_id', Filament::getTenant()?->id ?? null)
                    )
                    ->label(__('Batch Code'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\Select::make('sanitary_registry_id')
                            ->label('Registro Sanitario')
                            ->relationship('sanitary_registry', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('manufacturer_id')
                            ->label('Fabricante')
                            ->relationship('manufacturer', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Código de Lote')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('manufacturing_date')
                            ->label('Fecha de Fabricación')
                            ->required(),
                        Forms\Components\DatePicker::make('expiration_date')
                            ->label('Fecha de Caducidad')
                            ->required(),
                        Forms\Components\KeyValue::make('data')
                            ->label('Información Adicional')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ])
                    ->required()
                    ->disabled(fn (): bool => $this->ownerRecord->isDone())
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('Producto'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch.code')
                    ->label(__('Lote'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Cantidad'))
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label(__('Precio de Compra'))
                    ->sortable()
                    ->numeric()
                    ->prefix('$'),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->sortable()
                    ->numeric()
                    ->prefix('$'),
            ])
            ->headerActions([
                Action::make('confirmReception')
                    ->label('Confirmar Recepción')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->color('success')
                    ->hidden(fn (): bool => $this->ownerRecord->isDone())
                    ->action(function (Action $action): void {
                        try {
                            $wasConfirmed = app(ProductReceptionInventoryService::class)
                                ->confirm($this->ownerRecord);

                            $this->ownerRecord->refresh();

                            Notification::make()
                                ->title($wasConfirmed ? 'Recepción confirmada' : 'Recepción ya confirmada')
                                ->body($wasConfirmed
                                    ? 'Se agregó el inventario por lote y la recepción quedó en estado DONE.'
                                    : 'Esta recepción ya estaba confirmada, no se volvió a sumar inventario.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            $firstError = collect($exception->errors())->flatten()->first()
                                ?? 'No se pudo confirmar la recepción.';

                            Notification::make()
                                ->title('No se pudo confirmar')
                                ->body((string) $firstError)
                                ->danger()
                                ->send();

                            $action->cancel();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Error al confirmar')
                                ->body('Ocurrió un error inesperado al confirmar la recepción.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('splitQuantity')
                    ->label(__('Duplicar / Repartir'))
                    ->icon('heroicon-o-squares-plus')
                    ->color('warning')
                    ->hidden(fn (ProductReceptionItem $record): bool => (int) $record->quantity <= 1)
                    ->form([
                        Forms\Components\TextInput::make('quantity_for_new_lot')
                            ->label('Cantidad para el nuevo lote')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(fn (ProductReceptionItem $record): int => max(1, ((int) $record->quantity) - 1))
                            ->required()
                            ->helperText('La cantidad original se divide entre el registro actual y el nuevo.'),
                    ])
                    ->action(function (ProductReceptionItem $record, array $data): void {
                        try {
                            DB::transaction(function () use ($record, $data): void {
                                $originalQuantity = (int) $record->quantity;
                                $quantityForNewLot = (int) ($data['quantity_for_new_lot'] ?? 0);

                                if ($quantityForNewLot <= 0 || $quantityForNewLot >= $originalQuantity) {
                                    throw ValidationException::withMessages([
                                        'quantity_for_new_lot' => 'La cantidad del nuevo lote debe ser mayor a 0 y menor a la cantidad original.',
                                    ]);
                                }

                                $remainingQuantity = $originalQuantity - $quantityForNewLot;
                                $purchasePrice = (float) ($record->purchase_price ?? 0);

                                $record->update([
                                    'quantity' => $remainingQuantity,
                                    'total' => round($remainingQuantity * $purchasePrice, 2),
                                ]);

                                $newItem = $record->replicate();
                                $newItem->batch_id = null;
                                $newItem->quantity = $quantityForNewLot;
                                $newItem->total = round($quantityForNewLot * $purchasePrice, 2);
                                $newItem->save();
                            });

                            Notification::make()
                                ->title('Producto duplicado')
                                ->body('Se repartió la cantidad entre el registro actual y un nuevo ítem para otro lote.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            $firstError = collect($exception->errors())->flatten()->first()
                                ?? 'No se pudo repartir la cantidad.';

                            Notification::make()
                                ->title('No se pudo duplicar')
                                ->body((string) $firstError)
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Error al duplicar')
                                ->body('Ocurrió un error inesperado al repartir la cantidad.')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->label(__('Asignar Lote'))
                    ->hidden(fn (): bool => $this->ownerRecord->isDone()),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (): bool => $this->ownerRecord->isDone()),
                ])->hidden(fn (): bool => $this->ownerRecord->isDone())
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(fn (): bool => $this->ownerRecord->isDone()),
                ]),
            ]);
    }
}
