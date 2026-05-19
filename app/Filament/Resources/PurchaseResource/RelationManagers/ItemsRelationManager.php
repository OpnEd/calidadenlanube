<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use App\Filament\Resources\PurchaseResource;
use App\Helpers\PermissionVerificationHelper;
use App\Models\CentralProductPrice;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ItemsRelationManager extends RelationManager
{
    use \App\Traits\Operations\HasPurchaseItemsFormAndTable;
    
    protected static string $relationship = 'items';
    protected static ?string $title = 'Products';
    protected static ?string $model = Purchase::class;


    public function form(Form $form): Form
    {
        return static::buildPurchaseItemsForm($form);
    }

    public function table(Table $table): Table
    {
        return static::buildPurchaseItemsTable($table);
    }

    /* public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name'),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->numeric()
                    ->prefix('$'),
                Tables\Columns\TextColumn::make('total')
                    ->sortable()
                    ->numeric()
                    ->prefix('$'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar producto')
                    ->icon('phosphor-plus')
                    ->visible(fn(): bool => 
                        Gate::allows('confirm', $this->ownerRecord)
                        && in_array($this->ownerRecord->status, ['pending', 'in progress', 'in_progress'])
                    )
                    ->before(function (array $data, $action) {
                        $purchase = $this->ownerRecord;
                        $exists = $purchase->items()->where('product_id', $data['product_id'])->exists();
                        // si ya existe el producto en la orden, no permitir agregarlo de nuevo
                        if ($exists) {
                            \Filament\Notifications\Notification::make()
                                ->title('Este producto ya fue agregado')
                                ->body('No puedes agregar el mismo producto dos veces a la orden, pero sí puedes cambiar la cantidad en el registro.')
                                ->danger()
                                ->send();

                            // Cancelar la acción correctamente
                            $action->cancel();
                        }
                    })
                    ->after(function ($record) {
                        DB::transaction(function ($livewire) use ($record) {
                            $record->purchase->updatePurchaseTotal();
                        });
                    }),
                Tables\Actions\Action::make('confirmPurchase')
                    ->label('Convertir a Pedido')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->tooltip('Confirma esta cotización para convertirla en un pedido.')
                    ->visible(
                        fn(): bool =>
                        Gate::allows('confirm', $this->ownerRecord)
                            && $this->ownerRecord->items()->count() > 0
                            && in_array($this->ownerRecord->status, ['pending', 'in progress', 'in_progress'])
                    )
                    ->requiresConfirmation()
                    ->action(function () {
                        $purchase = $this->ownerRecord;
                        try {
                            DB::transaction(function () use ($purchase) {
                                $purchase->update([
                                    'status' => 'confirmed',
                                    'confirmed_at' => now(),
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Order confirmed')
                                ->color('success')
                                ->send();

                            // Redireccionar a la página 'index'
                            //return redirect()->route('filament.admin.resources.purchases.index');


                            // Redirigir al formulario de edición de este Purchase
                            Redirect::to(
                                PurchaseResource::getUrl('index')
                            );
                        } catch (\Exception $e) {

                            \Filament\Notifications\Notification::make()
                                ->title('Error al confirmar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->after(function ($record) {
                            DB::transaction(function ($livewire) use ($record) {
                                $record->purchase->updatePurchaseTotal();
                            });
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->after(function ($record) {
                            DB::transaction(function ($livewire) use ($record) {
                                $record->purchase->updatePurchaseTotal();
                            });
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    } */

    /* protected function afterCreate(): void
    {
        $this->recalculatePurchaseTotal();
    }

    protected function afterSave(): void
    {
        $this->recalculatePurchaseTotal();
    }

    protected function afterDelete(): void
    {
        $this->recalculatePurchaseTotal();
    }

    protected function recalculatePurchaseTotal(): void
    {
        $purchase = $this->getOwnerRecord(); // modelo Purchase
        $total = $purchase->items()->sum('total');

        $purchase->update(['total' => $total]);
    } */
}
