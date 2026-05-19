<?php

namespace App\Traits\Operations;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

trait HasInvoiceFormAndTable
{
    public static function buildInvoiceForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sale_id')
                    ->label('Código de venta')
                    ->disabled(),
                Forms\Components\Select::make('supplier_id')
                    ->label('ID del proveedor')
                    ->relationship('supplier', 'name')
                    ->preload(),
                Forms\Components\TextInput::make('code')
                    ->label(__('Código de factura'))
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
                    ->required(),
                Forms\Components\DatePicker::make('issued_date')
                    ->label(__('Fecha de emisión'))
                    ->default(now())
                    ->required(),
                Forms\Components\KeyValue::make('data')
                    ->label('Datos adicionales')
                    ->keyPlaceHolder('Conductor:')
                    ->valuePlaceHolder('Fulanito Peranez')
                    ->helperText('Puedes agregar cualquier información adicional relevante en formato JSON, por ejemplo: "Conductor": "Fulanito Peranez", "Tamaño": "XL"')
                    ->columnspanfull(),
            ]);
    }

    public static function buildInvoiceTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.id')
                    ->label('Código de venta')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Nombre del proveedor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Código de factura'))
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
                    ->label(__('Fecha de emisión'))
                    ->date()
                    ->sortable(),
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

    public static function buildCourseInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                
            ])->columns(1);
    }
}
