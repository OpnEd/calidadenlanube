<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeInvidadoController extends Controller
{
    public function showCode()
    {
        $url = route('home');
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->build();

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }
}
