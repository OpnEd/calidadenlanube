<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\CourseResource\Pages;
use App\Filament\TenantManager\Resources\Training\CourseResource\RelationManagers;
use App\Models\Quality\Training\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('objective')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required(),
                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->required(),
                Forms\Components\Select::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'technology' => 'Technology',
                        'business' => 'Business',
                        'design' => 'Design',
                        // Add more categories as needed
                    ])
                    ->required(),
                Forms\Components\Select::make('instructor_id')
                    ->relationship('instructor', 'name')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('course_images'),
                Forms\Components\Toggle::make('active')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
