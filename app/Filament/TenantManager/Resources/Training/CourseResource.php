<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\CourseResource\Pages;
use App\Filament\TenantManager\Resources\Training\CourseResource\RelationManagers;
use App\Traits\Filament\Training\HasCourseFormAndTable;
use App\Models\Quality\Training\Course;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    use HasCourseFormAndTable;

    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return static::buildCourseForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildCourseTable($table);
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
