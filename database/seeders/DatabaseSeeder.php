<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        public function run(): void
        {
            $this->call([
                RolesAndPermissionsSeeder::class,
                UserSeeder::class,
                SystemSettingSeeder::class,
                CategorySeeder::class,     // <--- ANTES DE PRODUCTOS
                ProductSeeder::class,      // <--- DESPUÉS DE CATEGORÍAS
                ClientSeeder::class,
                // QuoteSeeder::class, // Descomentar si tienes y quieres ejecutarlo
            ]);
        }
    }
    