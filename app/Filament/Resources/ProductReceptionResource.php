<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReceptionResource\Pages;
use App\Filament\Resources\ProductReceptionResource\RelationManagers;
use App\Models\ProductReception;
use Filament\Forms;
use Filament\Forms\Form;
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
                            ->relationship('purchase', 'code')
                            ->required(),
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'code')
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
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'in progress' => 'In Progress',
                                'done' => 'Done',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('reception_date'),
                        Forms\Components\Textarea::make('observations'),
                        Forms\Components\KeyValue::make('data'),
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
                Tables\Columns\TextColumn::make('purchase.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.id')
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductReceptions::route('/'),
            'create' => Pages\CreateProductReception::route('/create'),
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
