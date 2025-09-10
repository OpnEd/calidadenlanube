<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Models\Purchase;
use App\Filament\Resources\PurchaseResource;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SaleResource;
use App\Models\CentralProductPrice;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Redirect;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /* Action::make('createWithDefaults')
                ->label('Go shopping!')
                ->icon('phosphor-shopping-bag')
                ->action(function () {
                    // 1️⃣ Crear el Purchase con valores por defecto
                    $purchase = Purchase::create([
                        'team_id'       => Filament::getTenant()->id,
                        'supplier_id'   => 1,
                        'status'        => 'pending',  // ejemplo
                        'observations'  => null,
                        'total'         => 0,
                        'data'          => [],
                        // …otros campos por defecto…
                    ]);

                    // 2️⃣ Redirigir al formulario de edición de este Purchase
                    Redirect::to(
                        PurchaseResource::getUrl('edit', ['record' => $purchase->id])
                    );
                })
                ->color('primary'), */
            Action::make('quickPurchase')
                ->label('Iniciar Pedido!')
                ->icon('phosphor-shopping-bag')
                ->modalHeading(__('New Purchase'))
                ->form([
                    Forms\Components\Select::make('product_id')
                        ->options(
                            Product::query()->inStock()->get()->mapWithKeys(function ($product) {
                                return [$product->id => "{$product->name} ({$product->description})"];
                            })->toArray()
                        )
                        ->searchable()
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            // Calcular y persistir price y total aunque no haya inputs
                            $price = CentralProductPrice::find($get('product_id'))?->price ?? 0;
                            $set('price', $price);
                            $set('total', $state * $price);
                            
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

                    Forms\Components\Hidden::make('enlisted')
                        ->default(false),
                ])
                ->action(function (array $data, Action $action) {
                    // Validar que exista el producto
                    if (empty($data['product_id'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Debes seleccionar un producto')
                            ->color('danger')
                            ->send();
                        return;
                    }

                    // Crear la compra (Purchase) con los datos proporcionados
                    $purchase = Purchase::create([
                        'team_id'       => Filament::getTenant()->id,
                        'supplier_id'   => 1,
                        'code'          => (new Purchase())->generatePurchaseCode(),
                        'status'        => 'in progress',
                        'total'         => $data['total'] ?? 0,
                        'observations'  => null,
                        'data'          => [],
                    ]);

                    // Adjuntar el producto como item de la venta
                    $purchase->items()->create([
                        'product_id'     => $data['product_id'],
                        'quantity'       => $data['quantity'],
                        'price'          => $data['price'],
                        'total'          => $data['total'],
                        'enlisted'       => $data['enlisted'],
                    ]);

                    Notification::make()
                        ->title('Pedido creado exitosamente')
                        ->body('Puedes continuar editando el pedido para agregar más productos.')
                        ->icon('phosphor-check')
                        ->success()
                        ->send();

                    // Redirigir al formulario de edición de este Sale (donde ItemsRelationManager mostrará el item)
                    return Redirect::to(
                        PurchaseResource::getUrl('edit', ['record' => $purchase->id])
                    );
                })
                ->requiresConfirmation()
                ->visible(fn(): bool => Gate::allows('create', Purchase::class)),
        ];
    }

    /* public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'pending' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            'confirmed' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'confirmed')),
            'in progress' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in progress')),
            'ready' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'ready')),
            'dispatched' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'dispatched')),
            'delivered' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'delivered')),
        ];
    } */
}
