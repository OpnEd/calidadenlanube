<?php

namespace App\Livewire\Quality;

use Livewire\Component;
use App\Models\MinutesIvcSection;
use App\Models\MinutesIvcSectionEntry;

class PopulateMinutesIvc extends Component
{
    public function populateSections($teamId)
    {
        // 1. Poblar minutes_ivc_sections
        $sections = config('minutes-ivc-sections');
        foreach ($sections as $s) {
            MinutesIvcSection::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'order' => $s['order'],
                    'slug' => $s['slug'],
                    'name' => $s['name'],
                    'description' => $s['description'],
                    'status' => $s['status']
                ]
            );
        }

        // 2. Obtener IDs de las secciones
        $sectionNames = [
            'Cédula del establecimiento' => 'minutes_ivc_section_ced_est_id',
            'Recurso Humano' => 'minutes_ivc_section_rec_hum_id',
            'Infraestructura Física' => 'minutes_ivc_section_infr_fis_id',
            'Saneamiento de edificaciones' => 'minutes_ivc_section_san_edif_id',
            'Áreas' => 'minutes_ivc_section_areas_id',
            'Clasificación del Establecimiento' => 'minutes_ivc_section_clasif_estab_id',
            'Servicios Ofrecidos' => 'minutes_ivc_section_serv_ofr_id',
            'Otros aspectos' => 'minutes_ivc_section_otr_asp_id',
            'Sistema de gestión de calidad' => 'minutes_ivc_section_gest_cal_id',
            'Selección' => 'minutes_ivc_section_selec_id',
            'Adquisición' => 'minutes_ivc_section_adq_id',
            'Recepción' => 'minutes_ivc_section_recep_id',
            'Almacenamiento' => 'minutes_ivc_section_almac_id',
            'Dispensación' => 'minutes_ivc_section_dispe_id',
            'Devoluciones' => 'minutes_ivc_section_devol_id',
            'Manejo de Medicamentos Cadena de Frío' => 'minutes_ivc_section_cad_fri_id',
            'Inyectología' => 'minutes_ivc_section_inyect_id',
        ];
        $sectionIds = [];
        foreach ($sectionNames as $sectionName => $varName) {
            $sectionIds[$varName] = MinutesIvcSection::where('team_id', $teamId)
                ->where('name', $sectionName)
                ->first()
                ?->id;
        }

        // 3. Poblar entries de cada sección
        $sectionConfigMap = [
            'minutes_ivc_section_ced_est_id' => 'minutes-ivc-first-section-entries',
            // ... (agrega los demás como en tu código)
        ];
        foreach ($sectionConfigMap as $sectionVar => $configName) {
            $sectionId = $sectionIds[$sectionVar] ?? null;
            $entries = config($configName, []);
            if ($sectionId && is_array($entries)) {
                foreach ($entries as $e) {
                    MinutesIvcSectionEntry::updateOrCreate(
                        [
                            'minutes_ivc_section_id' => $sectionId,
                            'apply' => $e['apply'] ?? true,
                            'entry_id' => $e['entry_id'] ?? null,
                            'criticality' => $e['criticality'] ?? null,
                            'question' => $e['question'] ?? null,
                            'answer' => $e['answer'] ?? null,
                            'entry_type' => $e['entry_type'] ?? null,
                            'links' => $e['links'] ?? null,
                            'compliance' => $e['compliance'] ?? null,
                        ]
                    );
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.quality.populate-minutes-ivc');
    }
}