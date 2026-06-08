<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\ModuleResource\Pages;
use App\Filament\Resources\Quality\Training\ModuleResource\RelationManagers;
use App\Traits\Filament\Training\HasModuleFormAndTable;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Models\Quality\Training\Module;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class ModuleResource extends Resource
{
    use HasModuleFormAndTable;

    protected static ?string $model = Module::class;

    protected static ?string $navigationLabel = 'Módulos';

    protected static ?string $modelLabel = 'Módulo';

    protected static ?string $pluralModelLabel = 'Módulos';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return static::buildModuleForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildModuleTable($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::buildModuleInfolist($infolist);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'view' => Pages\ViewModule::route('/{record}'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }

    /* public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('course', fn (Builder $query) => $query->ownedByTeam(Filament::getTenant()?->id))
            ->with(['course'])
            ->withCount('lessons');
    } */

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()->id;

        return parent::getEloquentQuery()
            ->whereHas('course', function ($query) use ($tenantId) {
                $query->where(function ($q) use ($tenantId) {
                    $q->where('team_id', $tenantId)
                        ->orWhereNull('team_id');
                });
            });
    }
}
