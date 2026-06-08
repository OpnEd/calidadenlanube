<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\LessonResource\Pages;
use App\Models\Quality\Training\Lesson;
use App\Traits\Filament\Training\HasLessonFormAndTable;
use Filament\Facades\Filament;
use Filament\Infolists\Infolist;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonResource extends Resource
{
    use HasLessonFormAndTable;

    protected static ?string $model = Lesson::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Lecciones';

    protected static ?string $modelLabel = 'Leccion';

    protected static ?string $pluralModelLabel = 'Lecciones';

    protected static ?int $navigationSort = 30;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return static::buildLessonForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildLessonTable($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::buildLessonInfolist($infolist);
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
            'view' => Pages\ViewLesson::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        if ($tenantId === null) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('module.course', function (Builder $query) use ($tenantId) {
                $query->where(function (Builder $courseQuery) use ($tenantId) {
                    $courseQuery->where('team_id', $tenantId)
                        ->orWhereNull('team_id');
                });
            })
            ->with(['module.course']);
    }
}
