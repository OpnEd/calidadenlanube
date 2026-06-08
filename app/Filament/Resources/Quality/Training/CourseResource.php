<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\CourseResource\Pages;
use App\Filament\Resources\Quality\Training\CourseResource\RelationManagers;
use App\Traits\Filament\Training\HasCourseFormAndTable;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Models\Quality\Training\Course;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    use HasCourseFormAndTable;

    protected static ?string $model = Course::class;

    // Desactivamos el scope automático para poder incluir los cursos globales (null)
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Cursos';

    protected static ?string $modelLabel = 'Curso';

    protected static ?string $pluralModelLabel = 'Cursos';

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Universidad';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->discoverableToTeam(Filament::getTenant()?->id)
            ->with(['instructor'])
            ->withCount('enrollments');
    }

    public static function form(Form $form): Form
    {
        return static::buildCourseForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildCourseTable($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::buildCourseInfolist($infolist);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ModulesRelationManager::class,
            //RelationManagers\EnrollmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
