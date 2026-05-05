<?php

namespace App\Filament\Resources\Quality\Documentation;

use App\Filament\Resources\Quality\Documentation\DocumentResource\Pages;
use App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers\AcknowledgmentsRelationManager;
use App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers\ApprovalsRelationManager;
use App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers\DistributionsRelationManager;
use App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Quality\Documentation\DocumentResource\RelationManagers\VersionsRelationManager;
use App\Models\Document;
use App\Models\Process;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationGroup = 'Mejoramiento Continuo';
    protected static ?string $navigationLabel = 'Gestion Documental';
    protected static ?string $modelLabel = 'Documento';
    protected static ?string $pluralModelLabel = 'Documentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('process_id')
                    ->label('Proceso')
                    ->relationship(
                        name: 'process',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when(
                                $tenantId,
                                fn (Builder $q) => $q->whereNull('team_id')->orWhere('team_id', $tenantId)
                            );
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('code')
                            ->label('Codigo del proceso')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del proceso')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('process_type_id')
                            ->label('Tipo de proceso')
                            ->relationship('process_type', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del tipo')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label('Codigo del tipo')
                                    ->maxLength(50),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripcion')
                                    ->rows(2),
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripcion')
                            ->rows(2),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $data['team_id'] = Filament::getTenant()?->id;

                        return Process::create($data)->getKey();
                    })
                    ->required(),
                Forms\Components\Select::make('document_category_id')
                    ->label('Tipo documental')
                    ->relationship(
                        name: 'documentCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when(
                                $tenantId,
                                fn (Builder $q) => $q->whereNull('team_id')->orWhere('team_id', $tenantId)
                            );
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('owner_user_id')
                    ->label('Responsable')
                    ->relationship(
                        name: 'owner',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $tenantId = Filament::getTenant()?->id;

                            return $query->when(
                                $tenantId,
                                fn (Builder $q) => $q->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->where('teams.id', $tenantId))
                            );
                        }
                    )
                    ->searchable()
                    ->preload(),
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
                Forms\Components\DatePicker::make('effective_at')
                    ->label('Vigente desde'),
                Forms\Components\DatePicker::make('expires_at')
                    ->label('Vigente hasta'),
                Forms\Components\Repeater::make('data.objectives')
                    ->label('Objetivos')
                    ->schema([
                        Forms\Components\TextInput::make('objective')
                            ->label('Objetivo')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->defaultItems(1)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('data.general_conditions')
                    ->label('Condiciones generales')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('data.definitions')
                    ->label('Definiciones')
                    ->keyLabel('Termino')
                    ->valueLabel('Definicion')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('data.normative_references')
                    ->label('Referencias normativas')
                    ->keyLabel('Norma')
                    ->valueLabel('Referencia')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('data.procedure')
                    ->label('Procedimiento')
                    ->schema([
                        Forms\Components\TextInput::make('activity')
                            ->label('Actividad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('activity_description')
                            ->label('Descripcion de actividad')
                            ->rows(2)
                            ->required(),
                        Forms\Components\TextInput::make('responsible')
                            ->label('Responsable')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('records')
                            ->label('Registros')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('documentCategory.name')
                    ->label('Tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('process.name')
                    ->label('Proceso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currentVersion.version')
                    ->label('Version actual')
                    ->placeholder('Sin version'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_obsolete')
                    ->label('Obsoleto')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver'),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Document $record): bool => $record->status !== 'published'),
                    Tables\Actions\Action::make('version')
                        ->label('Versionar')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('primary')
                        ->url(fn (Document $record): string => DocumentVersionResource::getUrl('create') . '?document_id=' . $record->id)
                        ->visible(fn (Document $record): bool => $record->status === 'published'),
                    Tables\Actions\Action::make('send_to_review')
                        ->label('Enviar a revision')
                        ->requiresConfirmation()
                        ->action(fn (Document $record) => $record->update(['status' => 'in_review'])),
                    Tables\Actions\Action::make('publish')
                        ->label('Publicar')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Document $record): void {
                            $record->update([
                                'status' => 'published',
                                'is_obsolete' => false,
                            ]);

                            $record->currentVersion()?->update([
                                'status' => 'published',
                                'published_at' => now(),
                            ]);
                        }),
                    Tables\Actions\Action::make('obsolete')
                        ->label('Obsoleto')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Document $record): void {
                            $record->update([
                                'status' => 'obsolete',
                                'is_obsolete' => true,
                            ]);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Resumen del Documento')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Codigo')
                            ->placeholder('N/A')
                            ->weight('bold'),
                        TextEntry::make('title')
                            ->label('Titulo')
                            ->columnSpan(2),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge(),
                        TextEntry::make('currentVersion.version')
                            ->label('Version vigente')
                            ->placeholder('N/A'),
                        TextEntry::make('documentCategory.name')
                            ->label('Tipo documental')
                            ->placeholder('N/A'),
                        TextEntry::make('process.name')
                            ->label('Proceso')
                            ->placeholder('N/A'),
                        TextEntry::make('effective_at')
                            ->label('Vigente desde')
                            ->date('d/m/Y')
                            ->placeholder('N/A'),
                        TextEntry::make('expires_at')
                            ->label('Vigente hasta')
                            ->date('d/m/Y')
                            ->placeholder('N/A'),
                        IconEntry::make('is_obsolete')
                            ->label('Obsoleto')
                            ->boolean(),
                    ]),

                InfolistSection::make('Objetivos')
                    ->schema([
                        RepeatableEntry::make('structured_content.objectives')
                            ->label('')
                            ->state(
                                fn (Document $record): array => collect($record->structured_content['objectives'] ?? [])
                                    ->map(fn ($objective) => ['objective' => $objective])
                                    ->values()
                                    ->all()
                            )
                            ->schema([
                                TextEntry::make('objective')
                                    ->label('Objetivo'),
                            ])
                            ->contained(false),
                    ]),

                InfolistSection::make('Condiciones Generales')
                    ->schema([
                        TextEntry::make('structured_content.general_conditions')
                            ->label('')
                            ->state(fn (Document $record) => $record->structured_content['general_conditions'] ?? null)
                            ->html()
                            ->placeholder('Sin condiciones generales registradas.'),
                    ]),

                Grid::make(2)
                    ->schema([
                        InfolistSection::make('Definiciones')
                            ->schema([
                                KeyValueEntry::make('structured_content.definitions')
                                    ->label('')
                                    ->state(fn (Document $record) => $record->structured_content['definitions'] ?? []),
                            ]),
                        InfolistSection::make('Referencias Normativas')
                            ->schema([
                                KeyValueEntry::make('structured_content.normative_references')
                                    ->label('')
                                    ->state(fn (Document $record) => $record->structured_content['normative_references'] ?? []),
                            ]),
                    ]),

                InfolistSection::make('Procedimiento')
                    ->schema([
                        RepeatableEntry::make('structured_content.procedure')
                            ->label('')
                            ->state(fn (Document $record): array => $record->structured_content['procedure'] ?? [])
                            ->schema([
                                TextEntry::make('activity')
                                    ->label('Actividad'),
                                TextEntry::make('activity_description')
                                    ->label('Descripcion de actividad'),
                                TextEntry::make('responsible')
                                    ->label('Responsable'),
                                TextEntry::make('records')
                                    ->label('Registros'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
            ReviewsRelationManager::class,
            ApprovalsRelationManager::class,
            DistributionsRelationManager::class,
            AcknowledgmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->when($tenantId, fn (Builder $query) => $query->where('team_id', $tenantId));
    }
}
