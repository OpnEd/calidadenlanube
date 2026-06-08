<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;
use App\Filament\Resources\Quality\Training\EnrollmentResource\RelationManagers;
use App\Traits\Filament\Training\HasEnrollmentFormAndTable;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    use HasEnrollmentFormAndTable;

    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Matrículas';

    protected static ?string $modelLabel = 'Matrícula';

    protected static ?string $pluralModelLabel = 'Matrículas';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        $tenant = Filament::getTenant();

        // No mostrar el badge si no hay usuario o tenant
        if (!$user || !$tenant) {
            return null;
        }

        $count = Enrollment::where('user_id', $user->id)
            ->where('team_id', $tenant->id)
            ->where('status', 'in_progress')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Puedes elegir entre 'primary', 'success', 'warning', 'danger', 'info'
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return static::buildEnrollmentForm($form);
    }

    public static function table(Table $table): Table
    {
        return static::buildEnrollmentTable($table);
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
            'view' => Pages\CourseOverview::route('/{record}'),
            'lesson' => Pages\Lessonview::route('/{record}/lessons/{lesson}')
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;
        $user = Auth::user();

        $query = parent::getEloquentQuery()
            ->with(['user', 'course'])
            ->where('team_id', $tenantId);

        if ($user && ! ($user->isAdmin() || $user->isInstructor())) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
