<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\LessonResource\Pages;
use App\Filament\TenantManager\Resources\Training\LessonResource\RelationManagers;
use App\Traits\Filament\Training\HasLessonFormAndTable;
use App\Models\Quality\Training\Lesson;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class LessonResource extends Resource
{
    use HasLessonFormAndTable;

    protected static ?string $model = Lesson::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return static::buildLessonForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildLessonTable($table);
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
