<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;
use App\Filament\Resources\Quality\Training\EnrollmentResource\RelationManagers;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Matrículas';

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('team_id')
                    ->numeric(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('in_progress'),
                Forms\Components\TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('started_at'),
                Forms\Components\DateTimePicker::make('completed_at'),
                Forms\Components\DateTimePicker::make('last_accessed_at'),
                Forms\Components\DateTimePicker::make('certificated_at'),
                Forms\Components\TextInput::make('certificate_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('score_final')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('progress')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificated_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificate_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('score_final')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'lesson' => Pages\Lessonview::route('/{record}/lesson')
        ];
    }
}
