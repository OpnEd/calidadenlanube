<?php

namespace App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\RelationManagers;

use App\Models\CentralProductPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use App\Models\Purchase;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    use \App\Traits\Operations\HasPurchaseItemsFormAndTable;
    
    protected static string $relationship = 'items';
    protected static ?string $title = 'Productos';

    public function form(Form $form): Form
    {
        return static::buildPurchaseItemsForm($form);
    }

    public function table(Table $table): Table
    {
        return static::buildPurchaseItemsTable($table);
    }
}
