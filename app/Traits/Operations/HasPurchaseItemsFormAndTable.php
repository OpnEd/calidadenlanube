<?php

namespace App\Traits\Operations;

use App\Models\CentralProductPrice;
use App\Filament\Resources\PurchaseResource;
use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;


trait HasPurchaseItemsFormAndTable
{
    protected static ?string $model = Purchase::class;
    public static function buildPurchaseItemsForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship(
                        'product',
                        'name',
                        function (Builder $query) {
                            // Condicional según el panel
                            if (Filament::getCurrentPanel()->getId() === 'admin') {
                                $query->inStock();
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                        // Obtener la cantidad actual o 1 si es nulo
                        $quantity = $get('quantity') ?? 1;
                        // Buscar el precio actualizado del producto seleccionado
                        $price = CentralProductPrice::find($state)?->price ?? 0;
                        $set('price', $price);
                        $set('total', $quantity * $price);
                    })
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                        // Calcular y persistir price y total aunque no haya inputs
                        $price = CentralProductPrice::find($get('product_id'))?->price ?? 0;
                        $set('price', $price);
                        $set('total', $state * $price);
                    })
                    ->live(),
                Forms\Components\Hidden::make('price')
                    ->default(0),
                Forms\Components\Hidden::make('total')
                    ->default(0),
            ]);
    }

    public static function buildPurchaseItemsTable(Table $table): Table
    {
        return  $table
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
                    ->visible(
                        fn($livewire): bool =>
                        Gate::allows('confirm', $livewire->ownerRecord)
                            && in_array($livewire->ownerRecord->status, ['pending', 'in progress', 'in_progress'])
                    )
                    ->before(function (array $data, $action, $livewire) {
                        $purchase = $livewire->ownerRecord;
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
                        DB::transaction(function () use ($record) {
                            $record->purchase->updatePurchaseTotal();
                        });
                    }),
                Tables\Actions\Action::make('confirmPurchase')
                    ->label('Convertir a Pedido')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->tooltip('Confirma esta cotización para convertirla en un pedido.')
                    ->visible(
                        fn($livewire): bool =>
                        Gate::allows('confirm', $livewire->ownerRecord)
                            && $livewire->ownerRecord->items()->count() > 0
                            && in_array($livewire->ownerRecord->status, ['pending', 'in progress', 'in_progress'])
                    )
                    ->requiresConfirmation()
                    ->action(function ($livewire) {
                        $purchase = $livewire->ownerRecord;
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

                            $currentPanel = Filament::getCurrentPanel()->getId();
                            // Redirigir al formulario de edición de este Purchase
                            if ($currentPanel === 'admin') {
                                return Redirect::to(
                                    PurchaseResource::getUrl('index')
                                );
                            }

                            if ($currentPanel === 'tenantManager') {
                                return Redirect::to(
                                    OwnPurchaseResource::getUrl('index')
                                );
                            }
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
                            DB::transaction(function () use ($record) {
                                $record->purchase->updatePurchaseTotal();
                            });
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->after(function ($record) {
                            DB::transaction(function () use ($record) {
                                $record->purchase->updatePurchaseTotal();
                            });
                        }),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
