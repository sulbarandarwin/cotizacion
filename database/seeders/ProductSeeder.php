<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Product;
    use App\Models\Category;

    class ProductSeeder extends Seeder
    {
        public function run(): void
        {
            $catElectronicos = Category::where('name', 'Electrónicos')->first();
            $catFerreteria = Category::where('name', 'Ferretería')->first();
            $catOficina = Category::where('name', 'Oficina')->first();

            if ($catElectronicos) {
                Product::updateOrCreate(
                    ['code' => 'ELEC-001'],
                    [
                        'name' => 'Laptop Avanzada Pro 15"',
                        'description' => 'Laptop de 15 pulgadas con procesador i7, 16GB RAM, 512GB SSD.',
                        'cost' => 950.75,
                        'unit_of_measure' => 'Pza',
                        'category_id' => $catElectronicos->id,
                        'tax_type' => 'Gravado',
                    ]
                );
                Product::updateOrCreate(
                    ['code' => 'ELEC-002'],
                    [
                        'name' => 'Monitor Curvo 27" QHD',
                        'description' => 'Monitor curvo QHD de 144Hz.',
                        'cost' => 280.00,
                        'unit_of_measure' => 'Pza',
                        'category_id' => $catElectronicos->id,
                        'tax_type' => 'Gravado',
                    ]
                );
            }

            if ($catFerreteria) {
                Product::updateOrCreate(
                    ['code' => 'FERR-001'],
                    [
                        'name' => 'Juego de Destornilladores 20 Piezas',
                        'description' => 'Juego completo con puntas intercambiables.',
                        'cost' => 22.99,
                        'unit_of_measure' => 'Jgo',
                        'category_id' => $catFerreteria->id,
                        'tax_type' => 'Gravado',
                    ]
                );
            }

            if ($catOficina) {
                Product::updateOrCreate(
                    ['code' => 'OFIC-001'],
                    [
                        'name' => 'Resma de Papel Bond Carta',
                        'description' => 'Paquete de 500 hojas, 75g/m².',
                        'cost' => 5.25,
                        'unit_of_measure' => 'Resma',
                        'category_id' => $catOficina->id,
                        'tax_type' => 'Exento',
                    ]
                );
            }
             Product::updateOrCreate(
                ['code' => 'GEN-001'],
                [
                    'name' => 'Producto Genérico Test',
                    'description' => 'Descripción del producto genérico.',
                    'cost' => 10.00,
                    'unit_of_measure' => 'Und',
                    'category_id' => null, 
                    'tax_type' => 'Gravado',
                ]
            );
        }
    }
    