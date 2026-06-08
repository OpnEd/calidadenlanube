<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\AuditLogResource\Pages;
use App\Models\Quality\Training\AuditLog;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Auditoría';
    protected static ?string $modelLabel = 'Registro de Auditoría';
    protected static ?string $pluralModelLabel = 'Registros de Auditoría';
    protected static ?int $navigationSort = 50;

    public static function canAccess(): bool
    {
        return auth()->user()?->isInstructor() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Registro')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->disabled()
                            ->label('ID'),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Usuario'),
                        Forms\Components\TextInput::make('resource_type')
                            ->disabled()
                            ->label('Tipo de Recurso'),
                        Forms\Components\TextInput::make('resource_id')
                            ->disabled()
                            ->label('ID del Recurso'),
                        Forms\Components\TextInput::make('action')
                            ->disabled()
                            ->label('Acción'),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled()
                            ->label('Fecha/Hora'),
                    ])->columns(2),

                Forms\Components\Section::make('Descripción')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Información de Conexión')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->disabled()
                            ->label('Dirección IP')
                            ->copyable(),
                        Forms\Components\Textarea::make('user_agent')
                            ->disabled()
                            ->label('User Agent'),
                    ])->columns(1),

                Forms\Components\Section::make('Cambios Registrados')
                    ->schema([
                        Forms\Components\Textarea::make('changes')
                            ->disabled()
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width('80px'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('resource_type')
                    ->label('Recurso')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Course' => '📚 Curso',
                        'Module' => '📖 Módulo',
                        'Lesson' => '📄 Lección',
                        'Enrollment' => '👤 Matrícula',
                        'Certificate' => '🎓 Certificado',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'create' => 'Creado',
                        'update' => 'Actualizado',
                        'delete' => 'Eliminado',
                        'read' => 'Consultado',
                        'export' => 'Exportado',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'create' => 'success',
                        'update' => 'info',
                        'delete' => 'danger',
                        'read' => 'gray',
                        'export' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('resource_id')
                    ->label('ID Recurso')
                    ->sortable()
                    ->width('100px'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn (AuditLog $record) => $record->description),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        'create' => 'Creado',
                        'update' => 'Actualizado',
                        'delete' => 'Eliminado',
                        'read' => 'Consultado',
                        'export' => 'Exportado',
                    ]),
                
                SelectFilter::make('resource_type')
                    ->label('Tipo de Recurso')
                    ->options([
                        'Course' => 'Curso',
                        'Module' => 'Módulo',
                        'Lesson' => 'Lección',
                        'Enrollment' => 'Matrícula',
                        'Certificate' => 'Certificado',
                    ]),
                
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable(),
                
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for audit logs (read-only)
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            //'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('team_id', Filament::getTenant()?->id)
            ->with(['user']);
    }
}
