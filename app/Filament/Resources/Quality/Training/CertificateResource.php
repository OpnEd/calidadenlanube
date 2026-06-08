<?php

namespace App\Filament\Resources\Quality\Training;

use App\Filament\Resources\Quality\Training\CertificateResource\Pages;
use App\Models\Quality\Training\Certificate;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationGroup = 'Universidad';

    protected static ?string $navigationLabel = 'Certificados';

    protected static ?string $modelLabel = 'Certificado';

    protected static ?string $pluralModelLabel = 'Certificados';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('certificate_number')
                            ->label('Número de Certificado')
                            ->disabled()
                            ->helperText('Generado automáticamente'),

                        Forms\Components\Select::make('enrollment_id')
                            ->label('Inscripción')
                            ->relationship('enrollment', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->user->name} - {$record->course->title}")
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Estudiante')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('course_id')
                            ->label('Curso')
                            ->relationship('course', 'title')
                            ->disabled()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Detalles del Certificado')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3),

                        Forms\Components\TextInput::make('issuer')
                            ->label('Entidad Emisora')
                            ->required()
                            ->default('D-Origin 2.0'),

                        Forms\Components\DatePicker::make('issued_at')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->disabled(),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Válido Hasta')
                            ->helperText('Dejar vacío para certificados sin expiración'),

                        Forms\Components\TextInput::make('final_score')
                            ->label('Puntuación Final (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'issued' => 'Emitido',
                                'revoked' => 'Revocado',
                            ])
                            ->required(),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verificado')
                            ->helperText('Marca si el certificado ha sido verificado'),

                        Forms\Components\TextInput::make('template_used')
                            ->label('Plantilla Usada')
                            ->disabled()
                            ->helperText('Plantilla utilizada para generar el PDF'),
                    ])->columns(2),

                Forms\Components\Section::make('Notas Internas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(4)
                            ->helperText('Anotaciones privadas sobre este certificado'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificate_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Estudiante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Curso')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'issued',
                        'danger' => 'revoked',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'issued' => 'Emitido',
                        'revoked' => 'Revocado',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_score')
                    ->label('Puntuación')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ''))
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verificado')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Fecha de Emisión')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'issued' => 'Emitido',
                        'revoked' => 'Revocado',
                    ]),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verificado'),

                Tables\Filters\Filter::make('has_pdf')
                    ->label('Tiene PDF')
                    ->query(fn (Builder $query) => $query->whereNotNull('pdf_path')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (Certificate $record) => $record->getPdfDownloadUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Certificate $record) => $record->pdf_path !== null),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('issued_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
            'view' => Pages\ViewCertificate::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;
        $user = Auth::user();

        $query = parent::getEloquentQuery()
            ->with(['user', 'course', 'enrollment'])
            ->where('team_id', $tenantId);

        if ($user && ! ($user->isAdmin() || $user->isInstructor())) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
