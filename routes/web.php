<?php


use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\ProductReceptionController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RoleSeederController;
use App\Livewire\CustomerAutoRegister;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * Descargar, imprimir y enviar por correo electrónico facturas
 */
/* Route::middleware(['auth', 'team.context'])
    ->prefix('admin/{team}')
    ->group(function () { */
        Route::get('/generate-invoice-pdf/{id}', [InvoiceController::class, 'generatePdf'])->name('invoice.download');
        Route::get('/invoice/{id}/print', [InvoiceController::class, 'print'])->name('invoice.print');
        Route::post('/invoice/{id}/email', [InvoiceController::class, 'sendByEmail'])->name('invoice.email');
        Route::get('admin/{tenant}/documents/{document:slug}.pdf', [DocumentController::class, 'downloadPdf'])
            ->middleware('auth')
            ->name('documents.download');
        Route::get('admin/{tenant}/product-receptions/{productReception}.pdf', [ProductReceptionController::class, 'downloadPdf'])
            ->middleware('auth')
            ->name('product-receptions.download');
/*     }); */

Route::redirect('/', '/admin/login');

Route::get('/debug-session', function () {
    echo '<pre>';
    print_r(session()->all());
    echo '</pre>';
});

Route::get('/clear-session', function () {
    session()->flush();
    return 'Sesión vaciada';
});

Route::get('/clear-artisan', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Comandos Artisan ejecutados: optimize:clear, config:clear, cache:clear';
});

// Rutas para registro de nuevos clientes en la clínica
Route::get('/cliente/qr/autoregistro', [QrCodeController::class, 'showBarcode'])->name('customer.barcode');
//Route::get('/customer-auto-register', CustomerAutoRegister::class)
    //->name('customer.auto-register');

    Route::middleware(['auth'])->group(function () {
    
    // Ejecutar el seeder para un team
    Route::post('/admin/seed-roles/{teamId}', [RoleSeederController::class, 'seed'])
        ->name('seed-roles')
        ->where('teamId', '[0-9]+');
        
});
