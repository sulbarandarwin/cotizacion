<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Category;

    class CategorySeeder extends Seeder
    {
        public function run(): void
        {
            Category::updateOrCreate(
                ['name' => 'Electrónicos'],
                ['description' => 'Dispositivos y componentes electrónicos.']
            );
            Category::updateOrCreate(
                ['name' => 'Ferretería'],
                ['description' => 'Herramientas y materiales de ferretería.']
            );
            Category::updateOrCreate(
                ['name' => 'Oficina'],
                ['description' => 'Suministros y mobiliario de oficina.']
            );
            Category::updateOrCreate(
                ['name' => 'Sin Categoría Asignada'],
                ['description' => 'Productos que no tienen una categoría definida.']
            );
        }
    }
    
