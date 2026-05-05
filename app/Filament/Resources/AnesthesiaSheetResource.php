<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnesthesiaSheetResource\Pages;
use App\Filament\Resources\AnesthesiaSheetResource\RelationManagers;
use App\Filament\Schemas\PetFormSchema;
use App\Models\AnesthesiaSheet;
use App\Models\Customer;
use Filament\Forms;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

class AnesthesiaSheetResource extends Resource
{
    protected static ?string $model = AnesthesiaSheet::class;

    protected static ?string $navigationGroup = 'Clínica';
    protected static ?string $pluralModelLabel = 'Hojas de Anestesia';
    protected static ?string $modelLabel = 'Hoja de Anestesia';

    public static function getNavigationLabel(): string
    {
        return __('clinic.anesthesia_sheets');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('clinic.general_info'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('recipebook_id')
                            ->label(__('clinic.consecutive'))
                            ->relationship('recipebook', 'consecutive')
                            ->disabled(),

                        Forms\Components\Select::make('customer_id')
                            ->label(__('clinic.owner'))
                            ->searchable()
                            ->options(function () {
                                return Customer::where('team_id', Filament::getTenant()->id)
                                    ->get()
                                    ->mapWithKeys(function ($customer) {
                                        return [
                                            $customer->id => "{$customer->name} ({$customer->identification})"
                                        ];
                                    })->toArray();
                            })
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('pet_id')
                            ->label(__('clinic.patient'))
                            ->relationship(
                                name: 'pet',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, Get $get) {
                                    $customerId = $get('customer_id');
                                    if ($customerId) {
                                        $query->where('customer_id', $customerId);
                                    } else {
                                        // evita listar mascotas si no hay cliente seleccionado
                                        $query->whereRaw('0 = 1');
                                    }
                                }
                            )
                            ->preload()
                            ->required()
                            ->searchable()
                            ->disabled(fn(Get $get) => blank($get('customer_id')))
                            ->live()
                            ->createOptionForm(PetFormSchema::schema(false))
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    // Evita crear una mascota si no hay cliente seleccionado arriba
                                    ->visible(fn(Get $get) => filled($get('customer_id')))
                                    // 🔑 Inyecta el customer_id del formulario padre en los datos del createOptionForm
                                    ->mutateFormDataUsing(function (array $data, Get $get): array {
                                        $data['customer_id'] = $get('customer_id');
                                        return $data;
                                    });
                            })
                            ->editOptionForm(PetFormSchema::schema(false)),

                        Forms\Components\Select::make('surgeon_id')
                            ->label(__('clinic.surgeon'))
                            ->relationship('surgeon', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\DateTimePicker::make('anesthesia_start_time')
                            ->label(__('clinic.anesthesia_start_time'))
                            ->nullable(),
                    ]),

                Section::make(__('clinic.anamnesis'))
                    ->description(__('clinic.anamnesis_description'))
                    ->schema([
                        Forms\Components\KeyValue::make('anamnesis')
                            ->keyLabel(__('clinic.item'))
                            ->valueLabel(__('clinic.description'))
                            ->keyPlaceholder(__('clinic.placeholder_anamnesis_key'))
                            ->valuePlaceholder(__('clinic.placeholder_anamnesis_value'))
                            ->addActionLabel(__('clinic.add_item'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Section::make(__('clinic.anesthesia_notes'))
                    ->description(__('clinic.anesthesia_notes_description'))
                    ->schema([
                        Forms\Components\KeyValue::make('anesthesia_notes')
                            ->keyLabel(__('clinic.item'))
                            ->valueLabel(__('clinic.description'))
                            ->keyPlaceholder(__('clinic.placeholder_notes_key'))
                            ->valuePlaceholder(__('clinic.placeholder_notes_value'))
                            ->addActionLabel(__('clinic.add_item'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
                Forms\Components\DateTimePicker::make('anesthesia_end_time')
                    ->label(__('clinic.anesthesia_end_time'))
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipebook.consecutive')
                    ->label(__('clinic.recipe_number'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('pet.name')
                    ->label(__('clinic.pet_name'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('clinic.created_by'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('clinic.owner'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('surgeon.name')
                    ->label(__('clinic.surgeon'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'opened' => 'success',
                        'closed' => 'danger',
                        'canceled' => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('anesthesia_start_time')
                    ->label(__('clinic.anesthesia_start_time'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('anesthesia_end_time')
                    ->label(__('clinic.anesthesia_end_time'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('clinic.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('clinic.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('clinic.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('cancel')
                        ->label(__('clinic.cancel_sheet'))
                        ->color('danger')
                        ->action(function (AnesthesiaSheet $record) {
                            $record->update(['status' => 'canceled']);
                            Notification::make()
                                ->title(__('clinic.anesthesia_sheet_canceled'))
                                ->icon('phosphor-x-circle')
                                ->color('danger')
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Resumen')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Código')
                            ->placeholder('N/A')
                            ->weight('bold'),
                        TextEntry::make('recipebook.consecutive')
                            ->label(__('clinic.recipe_number'))
                            ->placeholder('N/A')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('status')
                            ->label(__('clinic.status'))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'opened' => 'success',
                                'closed' => 'danger',
                                'canceled' => 'warning',
                                default => 'gray',
                            }),
                        IconEntry::make('consumed')
                            ->label('Consumida')
                            ->boolean(),
                    ]),

                InfolistSection::make('Detalles Clínicos')
                    ->icon('heroicon-o-heart')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label(__('clinic.owner'))
                            ->placeholder('N/A'),
                        TextEntry::make('pet.name')
                            ->label(__('clinic.patient'))
                            ->placeholder('N/A'),
                        TextEntry::make('surgeon.name')
                            ->label(__('clinic.surgeon'))
                            ->placeholder('N/A'),
                        TextEntry::make('user.name')
                            ->label(__('clinic.created_by'))
                            ->placeholder('N/A'),
                        TextEntry::make('anesthesia_start_time')
                            ->label(__('clinic.anesthesia_start_time'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                        TextEntry::make('anesthesia_end_time')
                            ->label(__('clinic.anesthesia_end_time'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                        TextEntry::make('closed_at')
                            ->label('Cerrada el')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('N/A'),
                    ]),

                Grid::make(2)
                    ->schema([
                        InfolistSection::make('Anamnesis')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                KeyValueEntry::make('anamnesis')
                                    ->label(__('clinic.anamnesis'))
                                    ->placeholder('Sin registros'),
                            ]),
                        InfolistSection::make('Notas de Anestesia')
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                KeyValueEntry::make('anesthesia_notes')
                                    ->label(__('clinic.anesthesia_notes'))
                                    ->placeholder('Sin registros'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnesthesiaItemsRelationManager::class,
            RelationManagers\VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnesthesiaSheets::route('/'),
            'create' => Pages\CreateAnesthesiaSheet::route('/create'),
            'view' => Pages\ViewAnesthesiaSheet::route('/{record}'),
            'edit' => Pages\EditAnesthesiaSheet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
