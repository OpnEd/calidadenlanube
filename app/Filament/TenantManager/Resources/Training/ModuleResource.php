<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\ModuleResource\Pages;
use App\Filament\TenantManager\Resources\Training\ModuleResource\RelationManagers;
use App\Traits\Filament\Training\HasModuleFormAndTable;
use App\Models\Quality\Training\Module;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ModuleResource extends Resource
{
    use HasModuleFormAndTable;

    protected static ?string $model = Module::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return static::buildModuleForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildModuleTable($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
