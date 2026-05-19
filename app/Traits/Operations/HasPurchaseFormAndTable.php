<?php

namespace App\Traits\Operations;

use App\Enums\PurchaseStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;

trait HasPurchaseFormAndTable
{
    public static function buildPurchaseForm(Form $form): Form
    {
        return  $form
            ->schema([
                Section::make('Detalles del pedido')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->placeholder('Se generará automáticamente')
                            ->readOnly(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(PurchaseStatus::class)
                            ->default(PurchaseStatus::Pending)
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('total')
                            ->label('Total pedido')
                            ->readOnly()
                            ->prefix('$')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(4),

                Section::make('Información adicional')
                    ->schema([
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('data')
                            ->label('Metadatos')
                            ->keyPlaceHolder('Contacto de emergencia')
                            ->valuePlaceHolder('Juan Pérez, +123456789')
                            ->helperText('Agrega cualquier información adicional sobre el proveedor en formato clave-valor. Por ejemplo: "Contacto de emergencia": "Juan Pérez, +123456789".')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function buildPurchaseTable(Table $table): Table
    {
        return  $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Sin filtros por ahora
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //,
            ])
            ->defaultSort('created_at', 'desc');
    }
}
