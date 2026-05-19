<?php

namespace App\Filament\TenantManager\Resources\Operations;

use App\Filament\TenantManager\Resources\Operations\InvoiceResource\Pages;
use App\Filament\TenantManager\Resources\Operations\InvoiceResource\RelationManagers;
use App\Models\Dispatch;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Operaciones internas';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Facturas';
    protected static ?string $pluralModelLabel = 'Facturas';
    protected static ?string $modelLabel = 'Factura';
    protected static ?string $slug = 'operaciones/facturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('sale_id')
                    ->default(null),
                Forms\Components\Select::make('dispatch_id')
                    ->label('Despacho')
                    ->relationship(
                        'dispatch',
                        'id',
                        fn (Builder $query) => $query->whereNull('team_id')
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Dispatch $record): string => sprintf(
                            '%s - %s',
                            $record->purchase?->code ?? 'Despacho #' . $record->id,
                            $record->team?->name ?? 'Sin cliente'
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                        $dispatch = Dispatch::query()
                            ->with(['purchase.supplier', 'items'])
                            ->find($state);

                        $set('supplier_id', $dispatch?->purchase?->supplier_id);

                        if ($dispatch) {
                            $set('amount', (float) $dispatch->items->sum('total'));
                        }
                    }),
                Forms\Components\Select::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('code')
                    ->label('Codigo de factura')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Total'))
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Toggle::make('is_our')
                    ->label(__('Es una factura nuestra'))
                    ->inline(false)
                    ->default(true)
                    ->required(),
                Forms\Components\DatePicker::make('issued_date')
                    ->label('Fecha de emision')
                    ->default(now())
                    ->required(),
                Forms\Components\KeyValue::make('data')
                    ->label('Datos adicionales')
                    ->keyPlaceHolder('Conductor:')
                    ->valuePlaceHolder('Fulanito Peranez')
                    ->helperText('Puedes agregar cualquier informacion adicional relevante en formato JSON, por ejemplo: "Conductor": "Fulanito Peranez", "Tamano": "XL"')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dispatch.purchase.code')
                    ->label('Codigo de pedido')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dispatch.team.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Codigo de factura')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Total'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_our')
                    ->label(__('Es una factura nuestra'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issued_date')
                    ->label('Fecha de emision')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
