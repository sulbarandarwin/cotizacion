<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product; // Asegúrate de tener esta línea
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Desactivar revisión de llaves foráneas para evitar problemas si hay relaciones
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Product::truncate(); // Opcional: Vaciar la tabla antes de sembrar, si quieres empezar de cero cada vez

        $products = [
            ['code' => 'FER-001', 'name' => 'Martillo de Uña 16oz Stanley', 'unit_of_measure' => 'Unidad', 'cost' => 12.50],
            ['code' => 'FER-002', 'name' => 'Destornillador Phillips #2 Truper', 'unit_of_measure' => 'Unidad', 'cost' => 3.75],
            ['code' => 'FER-003', 'name' => 'Llave Ajustable 8" Crescent', 'unit_of_measure' => 'Unidad', 'cost' => 9.90],
            ['code' => 'FER-004', 'name' => 'Cinta Métrica 5m Stanley', 'unit_of_measure' => 'Unidad', 'cost' => 6.20],
            ['code' => 'FER-005', 'name' => 'Alicate Universal 7" Tramontina', 'unit_of_measure' => 'Unidad', 'cost' => 7.50],
            ['code' => 'FER-006', 'name' => 'Serrucho de Costilla 12" Irwin', 'unit_of_measure' => 'Unidad', 'cost' => 15.00],
            ['code' => 'FER-007', 'name' => 'Nivel de Burbuja 24" Stabila', 'unit_of_measure' => 'Unidad', 'cost' => 22.00],
            ['code' => 'FER-008', 'name' => 'Taladro Percutor 1/2" Bosch 550W', 'unit_of_measure' => 'Unidad', 'cost' => 65.00],
            ['code' => 'FER-009', 'name' => 'Juego de Brocas HSS (13 pzas)', 'unit_of_measure' => 'Juego', 'cost' => 18.50],
            ['code' => 'FER-010', 'name' => 'Disco de Corte Metal 4 1/2" Dewalt', 'unit_of_measure' => 'Unidad', 'cost' => 2.10],
            ['code' => 'FER-011', 'name' => 'Lija de Agua Grano 100 (Hoja)', 'unit_of_measure' => 'Hoja', 'cost' => 0.50],
            ['code' => 'FER-012', 'name' => 'Brocha Cerdas Naturales 2"', 'unit_of_measure' => 'Unidad', 'cost' => 3.00],
            ['code' => 'FER-013', 'name' => 'Rodillo Espuma Antigota 9"', 'unit_of_measure' => 'Unidad', 'cost' => 5.50],
            ['code' => 'FER-014', 'name' => 'Pintura Esmalte Blanco Brillante Galón', 'unit_of_measure' => 'Galón', 'cost' => 28.00],
            ['code' => 'FER-015', 'name' => 'Thinner Laca Litro', 'unit_of_measure' => 'Litro', 'cost' => 4.50],
            ['code' => 'FER-016', 'name' => 'Silicona Transparente Tubo 280ml', 'unit_of_measure' => 'Tubo', 'cost' => 3.80],
            ['code' => 'FER-017', 'name' => 'Clavos de Acero 2" (Kg)', 'unit_of_measure' => 'Kg', 'cost' => 2.50],
            ['code' => 'FER-018', 'name' => 'Tornillos Autorroscantes 1" (Caja 100)', 'unit_of_measure' => 'Caja', 'cost' => 4.00],
            ['code' => 'FER-019', 'name' => 'Tarugos Plásticos 6mm (Caja 100)', 'unit_of_measure' => 'Caja', 'cost' => 2.20],
            ['code' => 'FER-020', 'name' => 'Candado de Latón 40mm Hermex', 'unit_of_measure' => 'Unidad', 'cost' => 6.00],
            ['code' => 'FER-021', 'name' => 'Bisagra Tipo Libro 3" (Par)', 'unit_of_measure' => 'Par', 'cost' => 1.80],
            ['code' => 'FER-022', 'name' => 'Bombillo LED 9W Luz Cálida', 'unit_of_measure' => 'Unidad', 'cost' => 2.50],
            ['code' => 'FER-023', 'name' => 'Cable Eléctrico THHN #12 (Metro)', 'unit_of_measure' => 'Metro', 'cost' => 0.60],
            ['code' => 'FER-024', 'name' => 'Tomacorriente Doble con Tierra', 'unit_of_measure' => 'Unidad', 'cost' => 3.00],
            ['code' => 'FER-025', 'name' => 'Interruptor Sencillo', 'unit_of_measure' => 'Unidad', 'cost' => 2.20],
            ['code' => 'FER-026', 'name' => 'Cinta Aislante Negra 3M', 'unit_of_measure' => 'Rollo', 'cost' => 1.50],
            ['code' => 'FER-027', 'name' => 'Tubería PVC Sanitaria 2" x 3m', 'unit_of_measure' => 'Unidad', 'cost' => 7.00],
            ['code' => 'FER-028', 'name' => 'Codo PVC Sanitario 2" x 90°', 'unit_of_measure' => 'Unidad', 'cost' => 0.80],
            ['code' => 'FER-029', 'name' => 'Pegamento PVC Lata 1/4 Galón', 'unit_of_measure' => 'Lata', 'cost' => 9.00],
            ['code' => 'FER-030', 'name' => 'Llave de Paso Esférica 1/2" Bronce', 'unit_of_measure' => 'Unidad', 'cost' => 5.50],
            ['code' => 'FER-031', 'name' => 'Manguera de Jardín 15m Reforzada', 'unit_of_measure' => 'Unidad', 'cost' => 18.00],
            ['code' => 'FER-032', 'name' => 'Guantes de Carnaza Reforzados', 'unit_of_measure' => 'Par', 'cost' => 4.50],
            ['code' => 'FER-033', 'name' => 'Lentes de Seguridad Claros Uvex', 'unit_of_measure' => 'Unidad', 'cost' => 3.20],
            ['code' => 'FER-034', 'name' => 'Mascarilla Antipolvo N95 (Unidad)', 'unit_of_measure' => 'Unidad', 'cost' => 1.00],
            ['code' => 'FER-035', 'name' => 'Carretilla Metálica 60L Truper', 'unit_of_measure' => 'Unidad', 'cost' => 45.00],
            ['code' => 'FER-036', 'name' => 'Pala Cuadrada Mango Madera Bellota', 'unit_of_measure' => 'Unidad', 'cost' => 16.00],
            ['code' => 'FER-037', 'name' => 'Escoba Plástica Exterior', 'unit_of_measure' => 'Unidad', 'cost' => 5.00],
            ['code' => 'FER-038', 'name' => 'Extensión Eléctrica Uso Rudo 10m', 'unit_of_measure' => 'Unidad', 'cost' => 12.00],
            ['code' => 'FER-039', 'name' => 'Linterna LED Recargable', 'unit_of_measure' => 'Unidad', 'cost' => 9.50],
            ['code' => 'FER-040', 'name' => 'WD-40 Multiusos Lata 8oz', 'unit_of_measure' => 'Lata', 'cost' => 4.20],
            ['code' => 'FER-041', 'name' => 'Gato Hidráulico Botella 2 Ton', 'unit_of_measure' => 'Unidad', 'cost' => 25.00],
            ['code' => 'FER-042', 'name' => 'Cuerda de Nylon 1/4" (Metro)', 'unit_of_measure' => 'Metro', 'cost' => 0.30],
            ['code' => 'FER-043', 'name' => 'Malla Ciclón Galvanizada (Metro)', 'unit_of_measure' => 'Metro', 'cost' => 3.50],
            ['code' => 'FER-044', 'name' => 'Alambre de Púas Rollo 100m', 'unit_of_measure' => 'Rollo', 'cost' => 20.00],
            ['code' => 'FER-045', 'name' => 'Electrodo para Soldar 6013 1/8" (Kg)', 'unit_of_measure' => 'Kg', 'cost' => 5.00],
            ['code' => 'FER-046', 'name' => 'Careta para Soldar Fotosensible', 'unit_of_measure' => 'Unidad', 'cost' => 30.00],
            ['code' => 'FER-047', 'name' => 'Grasa Multiusos Litio Tubo', 'unit_of_measure' => 'Tubo', 'cost' => 3.00],
            ['code' => 'FER-048', 'name' => 'Teflón Sellador de Roscas Rollo', 'unit_of_measure' => 'Rollo', 'cost' => 0.80],
            ['code' => 'FER-049', 'name' => 'Aceite 3 en 1 Multiusos', 'unit_of_measure' => 'Unidad', 'cost' => 2.00],
            ['code' => 'FER-050', 'name' => 'Escalera de Aluminio Tijera 4 Pasos', 'unit_of_measure' => 'Unidad', 'cost' => 35.00],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Opcional: Reactivar revisión de llaves foráneas
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('¡Tabla de productos sembrada con 50 registros de ejemplo!');
    }
}
