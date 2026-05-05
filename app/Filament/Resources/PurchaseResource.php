<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Actions\ActionGroup;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationGroup = 'Transacciones';
    protected static ?string $navigationIcon = 'phosphor-shopping-cart-simple';
    protected static ?string $navigationLabel = 'Cotizaciones/Órd. Compras';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';
    protected static ?string $modelLabel = 'Orden de Compra';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'confirmed')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'confirmed')->count() > 10 ? 'warning' : 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Número de órdenes de compra confirmadas';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles de la cotización')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'in progress' => 'In Progress',
                                'ready' => 'Ready',
                                'dispatched' => 'Dispatched',
                                'delivered' => 'Delivered',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled(fn (?Purchase $record) => $record && ! in_array($record->status, ['pending', 'in progress'])),
                        Forms\Components\TextInput::make('total')
                            ->prefix('$')
                            ->readOnly()
                            ->disabled()
                            ->dehydrated(false) // para que no se guarde de nuevo en update
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('data')
                            ->label('Datos extra')
                            ->keyLabel('Ítem')
                            ->valueLabel('Descripción')
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('# Purchase')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'danger',
                        'confirmed' => 'primary',
                        'in progress' => 'warning',
                        'ready' => 'amber',
                        'dispatched' => 'gray',
                        'delivered' => 'success',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionsActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->visible(fn(Purchase $record): bool => $record->status === 'pending')
                        ->color('primary'),
                    Tables\Actions\EditAction::make()
                        ->visible(fn(Purchase $record): bool => $record->status === 'pending')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn(Purchase $record): bool => $record->status === 'pending'),
                    Tables\Actions\Action::make('confirm')
                        ->label('Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn(Purchase $record): bool =>
                            Gate::allows('confirm', $record)
                                && $record->status === 'pending'
                                && $record->items()->count() > 0
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Purchase')
                        ->modalDescription('Are you sure you want to confirm this purchase?')
                        ->action(function (Purchase $record) {
                            $record->status = 'confirmed';
                            $record->save();
                        }),
                    Tables\Actions\Action::make('receive')
                        ->label('Receive')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->visible(
                            fn(Purchase $record): bool =>
                            Gate::allows('receive', $record)
                                && $record->status === 'confirmed'
                                && $record->items()->count() > 0
                        )
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-check-badge')
                        ->modalIconColor('success')
                        ->modalHeading('Recibir compra')
                        ->modalDescription('Esta accion marcara la compra como delivered para habilitar la recepcion de producto.')
                        ->modalSubmitActionLabel('Si, marcar como delivered')
                        ->modalCancelActionLabel('Cancelar')
                        ->modalSubmitAction(fn (StaticAction $action) => $action->color('success'))
                        ->modalCancelAction(fn (StaticAction $action) => $action->color('gray'))
                        ->action(function (Purchase $record) {
                            $record->status = 'delivered';
                            $record->save();
                        }),
                    Tables\Actions\Action::make('clone_to_reception')
                        ->label('Clonar a Recepción')
                        ->icon('phosphor-copy-simple')
                        ->color('warning')
                        ->tooltip('Clona esta orden de compra a una nueva recepción de producto.')
                        ->visible(fn(Purchase $record): bool => $record->status === 'delivered')
                        ->requiresConfirmation()
                        ->modalHeading('Clonar a Recepción de Producto')
                        ->modalDescription('¿Está seguro de que desea clonar esta orden de compra a una recepción de producto?')
                        ->action(function (Purchase $record) {
                            // Crear la recepción de producto
                            $reception = \App\Models\ProductReception::create([
                                'team_id' => $record->team_id,
                                'user_id' => Auth::id(),
                                'purchase_id' => $record->id,
                                'invoice_id' => null,
                                'status' => 'in progress',
                                'reception_date' => now(),
                                'observations' => $record->observations,
                                'data' => $record->data,
                            ]);

                            // Clonar los items
                            foreach ($record->items as $item) {
                                \App\Models\ProductReceptionItem::create([
                                    'product_reception_id' => $reception->id,
                                    'product_id' => $item->product_id,
                                    'batch_id' => null,
                                    'quantity' => $item->quantity,
                                    'purchase_price' => $item->price,
                                    'total' => $item->total,
                                ]);
                            }
                            // Redirigir a la página de recepción
                            Redirect::to(
                                \App\Filament\Resources\ProductReceptionResource::getUrl('edit', ['record' => $reception->id])
                            );
                        }),
                ])
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
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
