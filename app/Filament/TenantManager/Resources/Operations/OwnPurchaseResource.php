<?php

namespace App\Filament\TenantManager\Resources\Operations;

use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\Pages;
use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\RelationManagers;
use App\Models\Purchase;
use App\Traits\Operations\HasPurchaseFormAndTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnPurchaseResource extends Resource
{
    use HasPurchaseFormAndTable;

    protected static ?string $model = Purchase::class;

    protected static ?string $navigationGroup = 'Operaciones internas';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Nuestros pedidos';
    protected static ?string $pluralModelLabel = 'Nuestros pedidos';
    protected static ?string $modelLabel = 'Nuestro pedido';
    protected static ?string $slug = 'operaciones-internas/nuestros-pedidos';

    public static function form(Form $form): Form
    {
        return static::buildPurchaseForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildPurchaseTable($table);
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
            'index' => Pages\ListOwnPurchases::route('/'),
            'create' => Pages\CreateOwnPurchase::route('/create'),
            'view' => Pages\ViewOwnPurchase::route('/{record}'),
            'edit' => Pages\EditOwnPurchase::route('/{record}/edit'),
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
