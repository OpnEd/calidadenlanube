<?php

namespace App\Filament\TenantManager\Resources\Training;

use App\Filament\TenantManager\Resources\Training\ModuleResource\Pages;
use App\Filament\TenantManager\Resources\Training\ModuleResource\RelationManagers;
use App\Models\Quality\Training\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationGroup = 'Training';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title'),
                Forms\Components\Textarea::make('objective'),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('duration'), // Duration in minute)s
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title'),
                Forms\Components\TextInput::make('order'),
                Forms\Components\TextInput::make('image'),
                Forms\Components\Checkbox::make('active')
                    ->default(true),
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
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
