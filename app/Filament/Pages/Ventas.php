<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Ventas extends Page
{
    //protected static ?string $navigationIcon = 'phosphor-cash-register';
    protected static ?string $navigationGroup = 'Transacciones';

    protected static string $view = 'filament.pages.ventas';
}
