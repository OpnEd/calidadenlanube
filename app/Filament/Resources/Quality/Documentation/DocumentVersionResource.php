<?php

namespace App\Filament\Resources\Quality\Documentation;

use App\Filament\Resources\Quality\Documentation\DocumentVersionResource\Pages;
use App\Models\Quality\Documentation\DocumentVersion;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentVersionResource extends Resource
{
    protected static ?string $model = DocumentVersion::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Versiones Documentales';
    protected static ?string $modelLabel = 'Version documental';
    protected static ?string $pluralModelLabel = 'Versiones documentales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('document_id')
                    ->label('Documento')
                    ->relationship(
                        name: 'document',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when($tenantId, fn (Builder $q) => $q->where('team_id', $tenantId));
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->default(fn () => request()->query('document_id'))
                    ->required(),
                Forms\Components\TextInput::make('version')
                    ->label('Version')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'in_review' => 'En revision',
                        'approved' => 'Aprobado',
                        'published' => 'Publicado',
                        'obsolete' => 'Obsoleto',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Toggle::make('is_current')
                    ->label('Version vigente')
                    ->default(false),
                Forms\Components\DatePicker::make('effective_at')
                    ->label('Vigente desde'),
                Forms\Components\DatePicker::make('expires_at')
                    ->label('Vigente hasta'),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Archivo')
                    ->disk('public')
                    ->directory('quality/documents/versions')
                    ->downloadable()
                    ->openable(),
                Forms\Components\Textarea::make('change_summary')
                    ->label('Resumen del cambio')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('body')
                    ->label('Contenido')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document.code')
                    ->label('Codigo documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document.title')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Version')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_current')
                    ->label('Vigente')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentVersions::route('/'),
            'create' => Pages\CreateDocumentVersion::route('/create'),
            'edit' => Pages\EditDocumentVersion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }
}
