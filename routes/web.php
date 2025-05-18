<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Importaciones de Controladores de Administración
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuoteController;

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

    // --- INICIO DE RUTAS DE ADMINISTRACIÓN (Opción B: sin prefijo 'admin.' en nombres/URLs) ---

    // Gestión de Productos
    Route::resource('products', ProductController::class);

    // Gestión de Clientes
    Route::resource('clients', ClientController::class);

    // Gestión de Cotizaciones
    Route::resource('quotes', QuoteController::class); // Define quotes.index, quotes.create, etc.

    // Rutas adicionales para Cotizaciones
    Route::get('quotes/{quote}/download-pdf', [QuoteController::class, 'downloadPDF'])->name('quotes.downloadPdf');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');
    Route::post('quotes/autosave/{quote?}', [QuoteController::class, 'autosave'])->name('quotes.autosave');
    Route::post('quotes/{quote}/change-status', [QuoteController::class, 'changeStatus'])->name('quotes.changeStatus');

    // Ruta para buscar productos (asegúrate que esté activa y correcta)
    // Usar una URL un poco distinta para evitar conflictos con la ruta resource de show: quotes/{id}
    Route::get('search-products-for-quotes', [QuoteController::class, 'searchProducts'])->name('quotes.searchProducts');

    // --- FIN DE RUTAS DE ADMINISTRACIÓN ---

}); // Fin del grupo de middleware

// Rutas de autenticación
require __DIR__.'/auth.php';