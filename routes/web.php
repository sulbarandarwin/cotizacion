<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Necesario para Auth::check()

// Importaciones de Controladores de Administración
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\SettingController;
use Spatie\Permission\Middleware\RoleMiddleware; // Para el middleware de rol de Spatie

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login'); 
});

// Rutas que requieren autenticación y correo verificado
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rutas de Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- INICIO DE RUTAS DE ADMINISTRACIÓN ---

    // Gestión de Productos
    Route::get('products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export.csv');
    Route::get('products/import', [ProductController::class, 'showImportForm'])->name('products.import.form'); // Ruta GET para mostrar el formulario
    Route::post('products/import', [ProductController::class, 'importProducts'])->name('products.import.process'); // Ruta POST para procesar el archivo
    Route::resource('products', ProductController::class); // Route::resource al final para evitar conflictos con rutas GET más específicas

    // Gestión de Clientes
    Route::resource('clients', ClientController::class);
    // Próximamente: Rutas de importación/exportación de clientes

    // Gestión de Cotizaciones
    // La ruta resource define la mayoría, pero especificamos las que reciben ID como parámetro simple
    // para que coincidan con la firma del método en el controlador que usa $quoteId.
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show'); // {quote} aquí usará Route Model Binding por defecto
    Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit'); // {quote} aquí usará Route Model Binding
    Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update'); // {quote} aquí usará Route Model Binding
    // Para duplicate y destroy, usamos {quoteId} para coincidir con los métodos que esperan el ID.
    Route::post('quotes/{quoteId}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');
    Route::delete('quotes/{quoteId}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    
    Route::get('quotes/{quote}/download-pdf', [QuoteController::class, 'downloadPDF'])->name('quotes.downloadPdf');
    Route::post('quotes/autosave/{quote?}', [QuoteController::class, 'autosave'])->name('quotes.autosave');
    Route::post('quotes/{quote}/change-status', [QuoteController::class, 'changeStatus'])->name('quotes.changeStatus');
    Route::get('search-products-for-quotes', [QuoteController::class, 'searchProducts'])->name('quotes.searchProducts');
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index'); // Asegurar que quotes.index esté definida
    // --- FIN DE RUTAS DE ADMINISTRACIÓN ---


    // Rutas para Configuración del Sistema (solo Administradores)
    Route::middleware([RoleMiddleware::class . ':Administrador']) 
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        });

}); // Fin del grupo de middleware principal

// Rutas de autenticación
require __DIR__.'/auth.php';
