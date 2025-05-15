<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Importaciones de Controladores de Administración
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuoteController; // Asegúrate que esta línea NO esté comentada
// use App\Http\Controllers\Admin\SystemSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas que requieren autenticación
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
    Route::resource('products', ProductController::class);

    // Gestión de Clientes
    Route::resource('clients', ClientController::class);

    // Gestión de Cotizaciones
    Route::resource('quotes', QuoteController::class);

    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/download-pdf', [QuoteController::class, 'downloadPDF'])->name('quotes.downloadPdf'); // NUEVA RUTA
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');

    Route::post('quotes/{quote}/autosave', [QuoteController::class, 'autosave'])->name('quotes.autosave');

    // Ruta para buscar productos (COMENTADA TEMPORALMENTE si no la usamos ahora)
    // Route::get('/quotes/search-products', [App\Http\Controllers\Admin\QuoteController::class, 'searchProducts'])->name('quotes.searchProducts');

    // --- FIN DE RUTAS DE ADMINISTRACIÓN ---

}); // Fin del grupo de middleware

// Rutas de autenticación
require __DIR__.'/auth.php';