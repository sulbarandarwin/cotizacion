<?php

namespace App\Http\Controllers\Admin; // Importante el namespace Admin

use App\Http\Controllers\Controller; // Controlador base de Laravel
use App\Models\Product;             // Nuestro modelo Product
use Illuminate\Http\Request;        // Para manejar las solicitudes HTTP

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Antes de mostrar, verificar permisos si es necesario
        // auth()->user()->can('ver_productos'); // Ejemplo con Spatie

        $products = Product::latest()->paginate(10); // Obtener los productos, los más nuevos primero, paginados
        return view('admin.products.index', compact('products')); // Pasamos los productos a la vista
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // auth()->user()->can('crear_productos');
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // auth()->user()->can('crear_productos');

        // Validación (la añadiremos en detalle después)
        $request->validate([
            'code' => 'required|string|max:255|unique:products,code',
            'name' => 'required|string|max:255',
            'unit_of_measure' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
        ]);

        Product::create($request->all()); // Crea el producto con los datos validados

        return redirect()->route('products.index')
                         ->with('success', 'Producto creado exitosamente.'); // Redirige con mensaje de éxito
    }

    /**
     * Display the specified resource.
     * Para este CRUD básico, show() puede ser opcional si la lista 'index' ya muestra suficiente.
     * O puedes tener una vista detallada.
     */
    public function show(Product $product)
    {
        // auth()->user()->can('ver_productos');
        // return view('admin.products.show', compact('product')); // Si tienes una vista show.blade.php
        return redirect()->route('products.index'); // Por ahora, redirigimos al index
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product) // Laravel inyecta automáticamente el Product por su ID
    {
        // auth()->user()->can('editar_productos');
        return view('admin.products.edit', compact('product')); // Pasamos el producto a editar a la vista
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // auth()->user()->can('editar_productos');

        $request->validate([
            'code' => 'required|string|max:255|unique:products,code,' . $product->id, // Ignorar el código del producto actual al validar unicidad
            'name' => 'required|string|max:255',
            'unit_of_measure' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
        ]);

        $product->update($request->all()); // Actualiza el producto

        return redirect()->route('products.index')
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // auth()->user()->can('eliminar_productos');

        $product->delete(); // Elimina el producto

        return redirect()->route('products.index')
                         ->with('success', 'Producto eliminado exitosamente.');
    }
}