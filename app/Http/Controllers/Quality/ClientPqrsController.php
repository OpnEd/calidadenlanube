<?php

namespace App\Http\Controllers\Quality;

use App\Filament\CustomerPanel\Pages\ClientPqrsRecord as ClientPqrsRecordPage;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class ClientPqrsController extends Controller
{
    /**
     * Genera y muestra el codigo QR que apunta al formulario PQRS del Team.
     */
    public function showQr(Team $team)
    {
        $url = ClientPqrsRecordPage::getUrl(
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
            labelText: 'Escanea para PQRS - ' . $team->name,
            labelFont: new Font(base_path('vendor/endroid/qr-code/assets/open_sans.ttf'), 14),
            labelAlignment: LabelAlignment::Center,
        );

        $result = $builder->build();

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }
}
