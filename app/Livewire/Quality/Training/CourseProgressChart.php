<?php

namespace App\Livewire\Quality\Training;

use Filament\Widgets\ChartWidget;

class CourseProgressChart extends ChartWidget
{
    protected static ?string $heading = 'Progreso del Curso';

    public float|int $progress = 0;

    protected function getData(): array
    {
        $completed = max(0, min(100, $this->progress));
        $remaining = 100 - $completed;

        return [
            'datasets' => [
                [
                    'label' => 'Progreso (%)',
                    'data' => [$completed, $remaining],
                    'backgroundColor' => [
                        '#10b981', // Emerald 500 (Success color)
                        '#e5e7eb', // Gray 200 (Background track)
                    ],
                    'borderWidth' => 0,
                    'cutout' => '75%', // This turns the pie chart into a thin progress ring
                ],
            ],
            'labels' => ['% Completado', '% Pendiente'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    // Hide the legend to keep the sidebar clean
                    'display' => false, 
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            // Remove the default scales since Doughnut charts don't use X/Y axes
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
            // Keep the chart responsive within the aside container
            'maintainAspectRatio' => false,
        ];
    }
}
