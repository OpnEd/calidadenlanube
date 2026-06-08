<?php

namespace App\Filament\Exporters;

use App\Models\Quality\Training\Enrollment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EnrollmentExporter extends Exporter
{
    protected static ?string $model = Enrollment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Estudiante'),
            ExportColumn::make('user.email')
                ->label('Email'),
            ExportColumn::make('course.title')
                ->label('Curso'),
            ExportColumn::make('status')
                ->label('Estado')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'not_started' => 'No Iniciado',
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    default => $state,
                }),
            ExportColumn::make('progress')
                ->label('Progreso (%)')
                ->formatStateUsing(fn ($state): string => "{$state}%"),
            ExportColumn::make('started_at')
                ->label('Fecha de Inicio')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('completed_at')
                ->label('Fecha de Completación')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('score_final')
                ->label('Calificación Final'),
            ExportColumn::make('certificated_at')
                ->label('Fecha del Certificado')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y') : 'N/A'),
            ExportColumn::make('created_at')
                ->label('Creado')
                ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
        ];
    }

    public static function getFileName(Export $export): string
    {
        return "enrollments-{$export->getKey()}";
    }
}
