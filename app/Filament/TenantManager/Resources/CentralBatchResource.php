<?php

namespace App\Filament\TenantManager\Resources;

use App\Filament\TenantManager\Resources\CentralBatchResource\Pages;
use App\Filament\TenantManager\Resources\CentralBatchResource\RelationManagers;
use App\Models\CentralBatch;
use App\Models\Manufacturer;
use App\Models\SanitaryRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Fieldset;
use App\Notifications\Notification;
use Illuminate\Support\Str;

class CentralBatchResource extends Resource
{
    protected static ?string $model = CentralBatch::class;

    protected static ?string $navigationGroup = 'Gestión de productos';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Lotes';
    protected static ?string $pluralModelLabel = 'Lotes';
    protected static ?string $modelLabel = 'Lote';
    protected static ?string $recordTitleAttribute = 'code';
    protected static ?string $slug = 'gestion-de-productos/lotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Registro sanitario')
                    ->schema([
                        Forms\Components\Select::make('sanitary_registry_id')
                            ->label('Registro sanitario')
                            ->relationship('sanitary_registry', 'code')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('code')
                                    ->label('Registro sanitario')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('cum')
                                    ->label('CUM')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return SanitaryRegistry::create($data)->getKey();
                            })
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Detalles')
                    ->schema([
                        Forms\Components\Select::make('manufacturer_id')
                            ->label('Fabricante')
                            ->relationship('manufacturer', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('identification')
                                    ->label('Identificación')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->label('Dirección')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phonenumber')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\KeyValue::make('data')
                                    ->label('Datos adicionales')
                                    ->helperText('Agrega cualquier dato adicional relevante para este lote en formato clave-valor. Ejemplo: "color: rojo", "tamaño: grande", etc.')
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Manufacturer::create($data)->getKey();
                            })
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('manufacturing_date')
                            ->label('Fecha de fabricación')
                            ->required(),
                        Forms\Components\DatePicker::make('expiration_date')
                            ->label('Fecha de vencimiento')
                            ->required(),
                    ]),
                Forms\Components\KeyValue::make('data')
                    ->label('Datos adicionales')
                    ->helperText('Agrega cualquier dato adicional relevante para este lote en formato clave-valor. Ejemplo: "color: rojo", "tamaño: grande", etc.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sanitary_registry.code')
                    ->label('Registro sanitario')
                    ->sortable(),
                Tables\Columns\TextColumn::make('manufacturer.name')
                    ->label('Fabricante')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('manufacturing_date')
                    ->label('Fecha de fabricación')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Fecha de vencimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->successNotification(fn(): Notification => Notification::make()
                            ->color('success')
                            ->success()
                            ->title('Lote actualizado')
                            ->body(Str::markdown('El **lote** fue actualizado correctamente.'))
                            ->icon('phosphor-barcode')
                            ->iconColor('success')
                            ->size('4xl')),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                // No bulk actions for now
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
            'index' => Pages\ListCentralBatches::route('/'),
            'create' => Pages\CreateCentralBatch::route('/create'),
            'edit' => Pages\EditCentralBatch::route('/{record}/edit'),
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
