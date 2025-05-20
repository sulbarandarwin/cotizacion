<?php

    namespace App\Exports;

    use App\Models\Product;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;
    use Maatwebsite\Excel\Concerns\WithMapping;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

    class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
    {
        public function collection()
        {
            return Product::with('category')
                          ->select('id', 'code', 'name', 'description', 'cost', 'unit_of_measure', 'tax_type', 'category_id', 'created_at', 'updated_at')
                          ->get();
        }

        public function headings(): array
        {
            return [
                'ID',
                'Código',
                'Nombre',
                'Descripción',
                'Costo',
                'Unidad de Medida',
                'Tipo de Impuesto',
                'Categoría ID',
                'Nombre Categoría',
                'Fecha Creación',
                'Fecha Actualización',
            ];
        }

        public function map($product): array
        {
            return [
                $product->id,
                $product->code,
                $product->name,
                $product->description,
                $product->cost,
                $product->unit_of_measure,
                $product->tax_type,
                $product->category_id,
                $product->category ? $product->category->name : 'N/A',
                $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : null,
                $product->updated_at ? $product->updated_at->format('Y-m-d H:i:s') : null,
            ];
        }
    }
    