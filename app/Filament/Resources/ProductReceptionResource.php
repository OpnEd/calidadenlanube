<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReceptionResource\Pages;
use App\Filament\Resources\ProductReceptionResource\RelationManagers;
use App\Models\Invoice;
use App\Models\ProductReception;
use App\Models\Purchase;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductReceptionResource extends Resource
{
    protected static ?string $model = ProductReception::class;

    protected static ?string $navigationGroup = 'Transacciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reception Details')
                    ->schema([
                        Forms\Components\Select::make('purchase_id')
                            ->relationship(
                                'purchase',
                                'code',
                                fn (Builder $query) => $query->where('team_id', Filament::getTenant()?->id)
                            )
                            ->createOptionForm([
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Proveedor')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('code')
                                    ->label('Código OC')
                                    ->default(fn (): string => (new Purchase())->generatePurchaseCode())
                                    ->required(),
                                Forms\Components\Hidden::make('status')
                                    ->default('delivered'),
                                Forms\Components\Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->columnSpanFull(),
                                Forms\Components\KeyValue::make('data')
                                    ->label('Datos extra')
                                    ->columnSpanFull(),
                                Forms\Components\Hidden::make('team_id')
                                    ->default(fn (): ?int => Filament::getTenant()?->id),
                                Forms\Components\Hidden::make('total')
                                    ->default(0),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['team_id'] = $data['team_id'] ?? Filament::getTenant()?->id;
                                $data['total'] = $data['total'] ?? 0;
                                $data['code'] = $data['code'] ?? (new Purchase())->generatePurchaseCode();
                                $data['status'] = 'delivered';

                                return Purchase::create($data)->getKey();
                            })
                            ->required(),
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship(
                                'invoice',
                                'code',
                                fn (Builder $query) => $query->where('team_id', Filament::getTenant()?->id))
                            ->createOptionForm([   // form para crear nueva Invoice “in-line”
                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Monto')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\Hidden::make('is_our')
                                    ->default(false),

                                Forms\Components\DatePicker::make('issued_date')
                                    ->label('Fecha de emisión')
                                    ->required(),

                                // Si necesitas vincular Supplier o Sale, puedes usar:
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Proveedor')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->nullable(),

                                // Datos adicionales:
                                Forms\Components\KeyValue::make('data')
                                    ->label('Datos extra'),
                                Forms\Components\Hidden::make('team_id')
                                    ->default(fn (): ?int => Filament::getTenant()?->id),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['team_id'] = $data['team_id'] ?? Filament::getTenant()?->id;

                                $invoice = Invoice::create([
                                    'team_id' => $data['team_id'],
                                    'code' => $data['code'],
                                    'amount' => $data['amount'],
                                    'is_our' => (bool) ($data['is_our'] ?? false),
                                    'issued_date' => $data['issued_date'],
                                    'supplier_id' => $data['supplier_id'] ?? null,
                                    'data' => $data['data'] ?? null,
                                ]);

                                return (int) $invoice->getKey();
                            })
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                ProductReception::STATUS_IN_PROGRESS => 'In Progress',
                                ProductReception::STATUS_DONE => 'Done',
                            ])
                            ->default(ProductReception::STATUS_IN_PROGRESS)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\DateTimePicker::make('reception_date'),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('data')
                            ->label('Datos extra')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.code')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.code')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('reception_date')
                    ->dateTime()
                    ->sortable(),
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
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (ProductReception $record): bool => ! $record->isDone()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Resumen de recepcion')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('purchase.code')
                            ->label('Purchase')
                            ->placeholder('N/A')
                            ->weight('bold'),
                        TextEntry::make('invoice.code')
                            ->label('Invoice')
                            ->placeholder('N/A'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                ProductReception::STATUS_IN_PROGRESS => 'In Progress',
                                ProductReception::STATUS_DONE => 'Done',
                                default => (string) $state,
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                ProductReception::STATUS_IN_PROGRESS => 'warning',
                                ProductReception::STATUS_DONE => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('reception_date')
                            ->label('Fecha recepcion')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                        TextEntry::make('team.name')
                            ->label('Team')
                            ->placeholder('N/A'),
                        TextEntry::make('user.name')
                            ->label('Recibido por')
                            ->placeholder('N/A'),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                        TextEntry::make('observations')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ]),

                Grid::make(3)
                    ->schema([
                        InfolistSection::make('Totales')
                            ->schema([
                                TextEntry::make('items_count')
                                    ->label('Lineas')
                                    ->state(fn (ProductReception $record): int => $record->items()->count()),
                                TextEntry::make('total_quantity')
                                    ->label('Cantidad total')
                                    ->state(fn (ProductReception $record): string => number_format((float) $record->items()->sum('quantity'), 2, ',', '.')),
                                TextEntry::make('total_amount')
                                    ->label('Valor total')
                                    ->state(fn (ProductReception $record): string => '$' . number_format((float) $record->items()->sum('total'), 2, ',', '.'))
                                    ->weight('bold'),
                            ]),
                        InfolistSection::make('Datos extra')
                            ->schema([
                                KeyValueEntry::make('data')
                                    ->label('')
                                    ->placeholder('Sin datos adicionales'),
                            ])
                            ->columnSpan(2),
                    ]),

                InfolistSection::make('Detalle por lote')
                    ->schema([
                        RepeatableEntry::make('items_detail')
                            ->label('')
                            ->state(fn (ProductReception $record): array => $record->items()
                                ->with(['product:id,name', 'batch:id,code'])
                                ->get()
                                ->map(fn ($item): array => [
                                    'product' => $item->product?->name ?? 'N/A',
                                    'batch' => $item->batch?->code ?? 'Sin lote',
                                    'quantity' => (float) $item->quantity,
                                    'purchase_price' => (float) $item->purchase_price,
                                    'total' => (float) $item->total,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('product')
                                    ->label('Producto'),
                                TextEntry::make('batch')
                                    ->label('Lote'),
                                TextEntry::make('quantity')
                                    ->label('Cantidad')
                                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', '.')),
                                TextEntry::make('purchase_price')
                                    ->label('Precio compra')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2, ',', '.')),
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->weight('bold')
                                    ->formatStateUsing(fn ($state): string => '$' . number_format((float) $state, 2, ',', '.')),
                            ])
                            ->columns(5),
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
            'index' => Pages\ListProductReceptions::route('/'),
            'create' => Pages\CreateProductReception::route('/create'),
            'view' => Pages\ViewProductReception::route('/{record}'),
            'edit' => Pages\EditProductReception::route('/{record}/edit'),
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
