<?php

namespace App\Http\Controllers\Quality;

use App\Filament\CustomerPanel\Pages\ClientSatisfactionEvaluation as ClientSatisfactionEvaluationPage;
use App\Http\Controllers\Controller;
use App\Models\Quality\Records\Clients\ClientSatisfactionEvaluation;
use App\Models\Team;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientSatisfactionController extends Controller
{
    /**
     * Genera y muestra el codigo QR que apunta al formulario de satisfaccion del Team.
     * Esta ruta suele ser protegida o usada desde el panel administrativo.
     */
    public function showQr(Team $team)
    {
        // La URL incluye el ID del equipo para saber a quien pertenece la evaluacion.
        $url = ClientSatisfactionEvaluationPage::getUrl(
            ['team' => $team->id],
            true,
            'customerPanel'
        );

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            labelText: 'Escanea para calificar - ' . $team->name,
            labelFont: new Font(base_path('vendor/endroid/qr-code/assets/open_sans.ttf'), 14),
            labelAlignment: LabelAlignment::Center,
        );

        $result = $builder->build();

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }

    /**
     * Muestra el formulario publico al cliente.
     */
    public function form(Team $team)
    {
        return view('quality.satisfaction.form', compact('team'));
    }

    /**
     * Guarda la evaluacion en la base de datos.
     */
    public function store(Request $request, Team $team)
    {
        // Validacion basica
        $validated = $request->validate([
            'service_area' => 'required|string',
            'overall_score' => 'required|integer|min:1|max:5',
            'attention_score' => 'nullable|integer|min:1|max:5',
            'waiting_time_score' => 'nullable|integer|min:1|max:5',
            'would_recommend' => 'required|boolean',
            'comments' => 'nullable|string|max:1000',
            // Puedes agregar validacion para los datos del cliente si decides pedirlos
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
        ]);

        DB::transaction(function () use ($validated, $team) {
            $evaluation = new ClientSatisfactionEvaluation();
            $evaluation->team_id = $team->id;
            $evaluation->channel = 'digital';
            $evaluation->evaluated_at = now();
            $evaluation->is_anonymous = empty($validated['client_name']); // Anonimo si no da nombre

            // Asignacion masiva controlada
            $evaluation->fill($validated);

            $evaluation->save();
        });

        return redirect()->back()->with('success', true);
    }
}
