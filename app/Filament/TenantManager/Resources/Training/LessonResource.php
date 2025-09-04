<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\LessonResource\Pages;
use App\Filament\TenantManager\Resources\Training\LessonResource\RelationManagers;
use App\Models\Quality\Training\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title'),
                Forms\Components\Textarea::make('objective'),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('duration'), // Duration in minute)s
                Forms\Components\Select::make('module_id')
                    ->relationship('module', 'title'),
                Forms\Components\TextInput::make('order'),
                Forms\Components\TextInput::make('video_url'),
                Forms\Components\TextInput::make('iframe'),
                Forms\Components\MarkdownEditor::make('content')->columnSpanFull(),
                Forms\Components\Checkbox::make('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
