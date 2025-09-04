<?php

namespace App\Filament\Pages\Quality;

use App\Models\Quality\Training\Enrollment;
use App\Models\TenantSetting;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CourseList extends Page
{
    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Cursos';

    protected static string $view = 'filament.pages.quality.course-list';

    public $tenantSetting;
    public $qualityPolicy;
    public $teamId;
    public $customTitle = 'Cursos Disponibles';
    public $customSubTitle = 'A continuación se muestra una lista de los cursos activos en los que puedes inscribirte.';
    public $customPageIcon = 'heroicon-o-academic-cap';

    /**
     * Cada elemento describe un enlace:
     *  - label: texto visible
     *  - color: color de Filament (info, success, etc.)
     *  - icon: icono de Phosphor
     *  - route: nombre de la ruta
     *  - params: (opcional) array de parámetros para route()
     */
    public array $headerLinks = [];

    public function mount()
    {
        $this->teamId = Filament::getTenant()->id;
        $this->qualityPolicy = TenantSetting::where('team_id', $this->teamId)->where('setting_id', 3)->first();
        $this->headerLinks = [
           /* [
                 'label'  => '9.12 - Capacitación',
                'color'  => 'info',
                'icon'   => 'phosphor-rewind',
                'route'  => 'filament.admin.pages.training-commitment',
                'params' => [$this->teamId], 
            ],*/
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.quality.header', [
            'customTitle' => $this->customTitle,
            'customSubtitle' => $this->customSubTitle,
            'teamId' => $this->teamId,
            'headerLinks' => $this->headerLinks,
            'customPageIcon' => $this->customPageIcon,
        ]);
    }
}
