<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\PurchaseResource\Pages;
use App\Filament\TenantManager\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use App\Notifications\Notification;
use App\Enums\PurchaseStatus;
use Filament\Tables\Enums\ActionsPosition;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationGroup = 'Operaciones externas';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Pedidos del cliente';
    protected static ?string $pluralModelLabel = 'Pedidos del cliente';
    protected static ?string $modelLabel = 'Pedido del cliente';
    protected static ?string $slug = 'operaciones-externas/pedidos-del-cliente';

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
                Section::make('Detalles del pedido')
                    ->schema([
                        Forms\Components\Placeholder::make('code')
                            ->label('Código')
                            ->inlineLabel()
                            ->content(fn ($record) => $record->code),
                        Forms\Components\Placeholder::make('team_name')
                            ->label('Cliente')
                            ->inlineLabel()
                            ->content(fn ($record) => $record->team?->name),
                        Forms\Components\Placeholder::make('status')
                            ->label('Estado')
                            ->inlineLabel()
                            ->content(fn ($record) => $record->status->getLabel()),
                        Forms\Components\Placeholder::make('total')
                            ->label('Total')
                            ->inlineLabel()
                            ->content(fn ($record) => $record->total),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->readOnly()
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('data')
                            ->label('Datos adicionales')
                            ->columnSpanFull()
                            ->disabled()
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->sortable(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Cliente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->icon(fn(PurchaseStatus $state) => $state->getIcon())
                    ->tooltip(fn(PurchaseStatus $state) => $state->getLabel()),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->prefix('$ '),
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Enlist items')
                        ->icon('phosphor-check-square')
                        ->visible(fn(Purchase $record): bool => $record->status->value === 'confirmed' && $record->items()->where('enlisted', '!=', 1)->exists()),
                    Action::make('createDispatch')
                        ->label('Dispatch')
                        ->icon('heroicon-o-truck')
                        ->action(function (Model $record, array $data): void {

                            // Verificar si todos los PurchaseItems están enlistados
                            if ($record->items()->where('enlisted', '!=', 1)->exists()) {
                                Notification::make()
                                    ->title('Falta verificar productos')
                                    ->color('danger')
                                    ->send();
                                return;
                            }

                            // Usar el servicio para crear el Dispatch y cambiar de estado
                            // a 'in progress'
                            $dispatch = app(\App\Services\DispatchService::class)->createFromPurchase($record);

                            Notification::make()
                                ->title('Despacho creado')
                                ->body('El despacho ha sido creado exitosamente y la compra está en progreso.')
                                ->success()
                                ->size('4xl') // Usando el tamaño de ejemplo
                                ->send();

                            // Redirigir al edit del Dispatch recién creado
                            Redirect::to(
                                \App\Filament\TenantManager\Resources\DispatchResource::getUrl('edit', ['record' => $dispatch->id])
                            );
                        })
                        ->requiresConfirmation()
                        ->color('info')
                        ->visible(fn(Purchase $record): bool => $record->status->value === 'confirmed' && $record->items()->where('enlisted', '!=', 1)->doesntExist()),
                    Action::make('editDispatch')
                        ->label('Edit Dispatch')
                        ->icon('phosphor-check-square')
                        ->action(function (Model $record, array $data): void {

                            // Redirigir al edit del Dispatch recién creado
                            Redirect::to(
                                \App\Filament\TenantManager\Resources\DispatchResource::getUrl('edit', ['record' => $record->id])
                            );
                        })
                        ->color('info')
                        ->visible(fn(Purchase $record): bool => $record->status->value === 'in progress'),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'view' => Pages\ViewPurchase::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('team_id')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
