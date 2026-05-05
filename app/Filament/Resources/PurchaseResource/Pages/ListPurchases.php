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
use Illuminate\Support\Facades\Log;

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
                    // Crear el Purchase con valores por defecto
                    $purchase = Purchase::create([
                        'team_id'       => Filament::getTenant()->id,
                        'supplier_id'   => 1,
                        'status'        => 'pending',  // ejemplo
                        'observations'  => null,
                        'total'         => 0,
                        'data'          => [],
                        // …otros campos por defecto…
                    ]);

                    // Redirigir al formulario de edición de este Purchase
                    Redirect::to(
                        PurchaseResource::getUrl('edit', ['record' => $purchase->id])
                    );
                })
                ->color('primary'), */
            Action::make('quickPurchase')
                ->label('Iniciar Cotización!')
                ->icon('phosphor-shopping-bag')
                ->modalHeading(__('Nueva Cotización'))
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
                            $price = CentralProductPrice::find($state)?->price ?? 0;
                            $quantity = $get('quantity') ?? 1;
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

                    Forms\Components\Hidden::make('enlisted')
                        ->default(false),
                ])
                ->action(function (array $data, Action $action) {
                    // DEBUG: Ver qué datos llegan del formulario
                    Log::info('QuickPurchase iniciada', ['data' => $data]);

                    // Validar que exista el producto
                    if (empty($data['product_id'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Debes seleccionar un producto')
                            ->color('danger')
                            ->send();
                        return;
                    }

                    // MEJORA: Recalcular precio en backend por seguridad y consistencia
                    // No confiamos ciegamente en $data['price'] o $data['total'] del frontend
                    $backendPrice = CentralProductPrice::find($data['product_id'])?->price ?? 0;
                    $backendTotal = $backendPrice * ($data['quantity'] ?? 1);

                    Log::info('Precios calculados', ['price' => $backendPrice, 'total' => $backendTotal]);

                    // Crear la compra (Purchase) con los datos proporcionados
                    $purchase = Purchase::create([
                        'team_id'       => Filament::getTenant()->id,
                        'supplier_id'   => 1,
                        'code'          => (new Purchase())->generatePurchaseCode(),
                        'status'        => 'pending',
                        'total'         => $backendTotal,
                        'observations'  => null,
                        'data'          => [],
                    ]);

                    // Adjuntar el producto como item de la venta
                    $purchase->items()->create([
                        'product_id'     => $data['product_id'],
                        'quantity'       => $data['quantity'],
                        'price'          => $backendPrice,
                        'total'          => $backendTotal,
                        'enlisted'       => $data['enlisted'],
                    ]);

                    Notification::make()
                        ->title('Cotización iniciada con éxito')
                        ->body('Puedes continuar editando la cotización para agregar más productos.')
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
