<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\ManagementIndicator;
use App\Services\IndicatorService;
use Filament\Support\RawJs;

trait HasIndicatorTooltip
{
    protected function indicatorTooltipExtraJsOptionsFromIndicatorName(string $indicatorName, ?int $teamId = null): ?RawJs
    {
        $teamId ??= \Filament\Facades\Filament::getTenant()?->id;

        $indicator = null;
        if ($teamId) {
            $indicator = app(IndicatorService::class)->getIndicatorConfig($teamId, $indicatorName);
        }

        return $this->indicatorTooltipExtraJsOptionsFromIndicator($indicator);
    }

    protected function indicatorTooltipExtraJsOptionsFromIndicator(?ManagementIndicator $indicator): ?RawJs
    {
        return $this->indicatorTooltipExtraJsOptionsFromValues(
            $indicator?->objective,
            $indicator?->description,
            $indicator?->information_source,
            $indicator?->pivot?->indicator_goal
        );
    }

    protected function indicatorTooltipExtraJsOptionsFromValues(
        ?string $objective,
        ?string $description,
        ?string $source,
        $goal = null
    ): ?RawJs
    {
        $info = [
            'objective' => e($objective ?: 'No definido'),
            'description' => e($description ?: 'No definido'),
            'source' => e($source ?: 'No definido'),
            'goal' => e(($goal === null || $goal === '') ? 'No definido' : (string) $goal),
        ];

        $infoJson = json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $js = <<<'JS'
({
    tooltip: {
        custom: function({series, seriesIndex, dataPointIndex, w}) {
            const info = __INFO__;
            const label = (w && w.globals && w.globals.labels) ? w.globals.labels[dataPointIndex] : '';
            const value = (series && series[seriesIndex]) ? series[seriesIndex][dataPointIndex] : null;
            const valueHtml = (value !== null && value !== undefined)
                ? `<div><strong>Valor:</strong> ${value}</div>`
                : '';

            return `<div class="apexcharts-tooltip-title">${label}</div>
                <div class="px-2 py-1 text-xs space-y-1" style="max-width: 260px; white-space: normal; word-break: break-word;">
                    ${valueHtml}
                    <div><strong>Objetivo:</strong> ${info.objective}</div>
                    <div><strong>Descripcion:</strong> ${info.description}</div>
                    <div><strong>Fuente:</strong> ${info.source}</div>
                    <div><strong>Meta:</strong> ${info.goal}</div>
                </div>`;
        }
    }
})
JS;

        $js = str_replace('__INFO__', $infoJson, $js);

        return RawJs::make($js);
    }
}
