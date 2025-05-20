<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Para validación manual adicional si es necesaria

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $skippedCount = 0;
    private $errors = [];
    private $processedRows = 0;

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) 
        {
            $this->processedRows++;
            // Convertir la fila a array para poder usar Validator::make si es necesario
            $rowData = $row->toArray();

            // Validar datos básicos requeridos antes de intentar cualquier cosa
            $validator = Validator::make($rowData, $this->rules(), $this->customValidationMessages());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Fila " . ($rowIndex + 2) . ": " . $error; // +2 porque WithHeadingRow y los arrays son base 0
                }
                $this->skippedCount++;
                continue; // Saltar esta fila
            }

            $category = null;
            if (!empty($rowData['categoria_nombre'])) {
                $category = Category::firstOrCreate(
                    ['name' => trim($rowData['categoria_nombre'])],
                    ['description' => 'Categoría importada automáticamente']
                );
            }

            try {
                $product = Product::updateOrCreate(
                    [
                        'code' => trim($rowData['codigo'])
                    ],
                    [
                        'name' => trim($rowData['nombre']),
                        'description' => isset($rowData['descripcion']) ? trim($rowData['descripcion']) : null,
                        'cost' => (float)str_replace(',', '.', $rowData['costo'] ?? 0),
                        'unit_of_measure' => isset($rowData['unidad_medida']) ? trim($rowData['unidad_medida']) : 'Pza',
                        'category_id' => $category ? $category->id : null,
                        'tax_type' => isset($rowData['tipo_impuesto']) ? trim($rowData['tipo_impuesto']) : 'Gravado',
                    ]
                );

                if ($product->wasRecentlyCreated) {
                    $this->importedCount++;
                } elseif ($product->wasChanged()) { // Verifica si alguno de los atributos que intentaste actualizar realmente cambió
                    $this->updatedCount++;
                } else {
                    // Si no fue creado recientemente y no cambió, se podría contar como "sin cambios" o "ya existente"
                    // Para este conteo, no lo añadimos a imported ni updated, podría ser un skip si se quiere.
                }

            } catch (\Illuminate\Database\QueryException $e) {
                $this->skippedCount++;
                $this->errors[] = "Fila " . ($rowIndex + 2) . " (Código: {$rowData['codigo']}): Error de base de datos - " . $e->getMessage();
                Log::error("Error de BD importando producto", ['codigo' => $rowData['codigo'], 'error' => $e->getMessage()]);
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = "Fila " . ($rowIndex + 2) . " (Código: {$rowData['codigo']}): Error general - " . $e->getMessage();
                Log::error("Error general importando producto", ['codigo' => $rowData['codigo'], 'error' => $e->getMessage()]);
            }
        }
    }

    public function rules(): array
    {
        // Estas reglas se aplican por Maatwebsite ANTES de llegar al método collection si WithValidation está activo.
        // Las claves deben coincidir EXACTAMENTE con las cabeceras del archivo Excel/CSV.
        return [
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'costo' => 'required|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:50',
            'categoria_nombre' => 'nullable|string|max:255',
            'tipo_impuesto' => 'nullable|string|in:Gravado,Exento,No Sujeto', // Asegúrate que estos sean tus valores exactos
            'descripcion' => 'nullable|string|max:65535', // text
        ];
    }

    public function customValidationMessages()
    {
        return [
            'codigo.required' => 'El campo CÓDIGO es obligatorio.',
            'nombre.required' => 'El campo NOMBRE es obligatorio.',
            'costo.required' => 'El campo COSTO es obligatorio.',
            'costo.numeric' => 'El COSTO debe ser un número válido.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Este método se llama si WithValidation falla para una fila ANTES de ToCollection.
        // Los errores aquí ya están formateados por Maatwebsite.
        foreach ($failures as $failure) {
            $this->errors[] = "Error de validación en fila {$failure->row()}: " . implode(', ', $failure->errors()) . " para el atributo '{$failure->attribute()}' (Valor: " . ($failure->values()[$failure->attribute()] ?? 'N/A') . ")";
        }
        $this->skippedCount += count($failures); // Contar filas saltadas por fallas de validación inicial
    }
    
    public function getImportedCount(): int { return $this->importedCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getProcessedRows(): int { return $this->processedRows; }
    public function getErrors(): array { return $this->errors; }
}
