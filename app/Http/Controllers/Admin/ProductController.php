<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport; // <--- IMPORTACIÓN
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'unit_of_measure' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'tax_type' => 'required|string|in:Gravado,Exento,No Sujeto',
            'image_path_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('image_path_file')) {
            $path = $request->file('image_path_file')->store('products', 'public');
            $validatedData['image_path'] = $path;
        }

        Product::create($validatedData);
        return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        if (!$product || !$product->exists) {
             return redirect()->route('products.index')->with('error', 'Producto no encontrado.');
        }
        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'categories')); 
    }

    public function edit(Product $product)
    {
        if (!$product || !$product->exists) {
             return redirect()->route('products.index')->with('error', 'Producto no encontrado para editar.');
        }
        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if (!$product || !$product->exists) {
             return redirect()->route('products.index')->with('error', 'Producto no encontrado para actualizar.');
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'unit_of_measure' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'tax_type' => 'required|string|in:Gravado,Exento,No Sujeto',
            'image_path_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('image_path_file')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $path = $request->file('image_path_file')->store('products', 'public');
            $validatedData['image_path'] = $path;
        }

        $product->update($validatedData);
        return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
         if (!$product || !$product->exists) {
             return redirect()->route('products.index')->with('error', 'Producto no encontrado para eliminar.');
        }
        try {
            if ($product->quoteItems()->exists()) {
                return redirect()->route('products.index')->with('error', 'No se pudo eliminar el producto porque está siendo utilizado en una o más cotizaciones.');
            }

            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->delete();
            return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error al eliminar producto ID {$product->id}: " . $e->getMessage());
            $errorMessage = 'No se pudo eliminar el producto.';
            if (str_contains($e->getMessage(), '1451')) { 
                $errorMessage = 'No se pudo eliminar el producto porque está siendo utilizado en cotizaciones u otras partes del sistema.';
            }
            return redirect()->route('products.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error("Error general al eliminar producto ID {$product->id}: " . $e->getMessage());
            return redirect()->route('products.index')->with('error', 'Ocurrió un error inesperado al intentar eliminar el producto.');
        }
    }

    public function exportExcel()
    {
        // Opcional: Verificar permiso
        // if (!Auth::user() || !Auth::user()->can('exportar productos')) {
        //     abort(403, 'No tiene permisos para exportar productos.');
        // }
        return Excel::download(new ProductsExport, 'productos-' . now()->format('Y-m-d-His') . '.xlsx');
    }

    public function exportCsv()
    {
        // Opcional: Verificar permiso
        // if (!Auth::user() || !Auth::user()->can('exportar productos')) {
        //     abort(403, 'No tiene permisos para exportar productos.');
        // }
        return Excel::download(new ProductsExport, 'productos-' . now()->format('Y-m-d-His') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    // --- MÉTODOS PARA IMPORTACIÓN ---
    public function showImportForm()
    {
        // Opcional: Verificar permiso
        // if (!Auth::user() || !Auth::user()->can('importar productos')) {
        //     abort(403, 'No tiene permisos para importar productos.');
        // }
        return view('admin.products.import');
    }

    public function importProducts(Request $request)
    {
        // Opcional: Verificar permiso
        // if (!Auth::user() || !Auth::user()->can('importar productos')) {
        //     abort(403, 'No tiene permisos para importar productos.');
        // }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        $import = new ProductsImport();
        
        try {
            Excel::import($import, $request->file('import_file'));
            
            $importedCount = $import->getImportedCount();
            $updatedCount = $import->getUpdatedCount();
            $skippedCount = $import->getSkippedCount();
            $processedRows = $import->getProcessedRows();
            $errors = $import->getErrors();

            $message = "Procesadas {$processedRows} filas. ";
            $message .= "Productos nuevos importados: {$importedCount}. ";
            $message .= "Productos actualizados: {$updatedCount}. ";
            
            if ($skippedCount > 0) {
                $message .= "Filas omitidas/con error: {$skippedCount}.";
                Log::warning("Errores durante la importación de productos:", $errors);
                return redirect()->route('products.import.form')
                                 ->with('warning', $message) // Cambiado a warning si hay skips
                                 ->with('import_detailed_errors', $errors); 
            }

            return redirect()->route('products.index')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                 $errorMessages[] = 'Fila ' . $failure->row() . ': ' . implode(', ', $failure->errors()) . ' (Valor: ' . ($failure->values()[$failure->attribute()] ?? 'N/A') . ')';
             }
             Log::warning("Errores de validación durante la importación de productos:", $errorMessages);
             return redirect()->route('products.import.form')->with('import_errors', $errorMessages);
        } catch (\Exception $e) {
            Log::error('Error general durante la importación de productos: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('products.import.form')->with('error', 'Error general durante la importación: ' . $e->getMessage());
        }
    }
}
