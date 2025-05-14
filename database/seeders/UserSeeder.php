<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Para crear usuarios
use Illuminate\Support\Facades\Hash; // Para encriptar contraseñas
use Spatie\Permission\Models\Role; // Para asignar roles

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Usuario Administrador
        $adminUser = User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@example.com', // Cambia esto por tu email real si quieres
            'password' => Hash::make('password'), // ¡CAMBIA ESTA CONTRASEÑA por una segura!
            // 'email_verified_at' => now(), // Opcional: marcar el email como verificado
        ]);
        // Asignar rol de Administrador
        $adminRole = Role::findByName('Administrador');
        if ($adminRole) {
            $adminUser->assignRole($adminRole);
        } else {
            // Opcional: Manejar el caso de que el rol no exista, aunque debería por el seeder anterior
            $this->command->error("El rol 'Administrador' no fue encontrado. Ejecuta RolesAndPermissionsSeeder primero.");
        }

        // Crear Usuario Vendedor de Ejemplo
        $vendedorUser = User::create([
            'name' => 'Vendedor Ejemplo',
            'email' => 'vendedor@example.com',
            'password' => Hash::make('password123'), // ¡CAMBIA ESTA CONTRASEÑA!
            // 'email_verified_at' => now(), // Opcional
        ]);
        // Asignar rol de Vendedor
        $vendedorRole = Role::findByName('Vendedor');
        if ($vendedorRole) {
            $vendedorUser->assignRole($vendedorRole);
        } else {
            $this->command->error("El rol 'Vendedor' no fue encontrado. Ejecuta RolesAndPermissionsSeeder primero.");
        }

        // Puedes añadir más usuarios aquí si quieres
        $this->command->info('Usuarios Administrador y Vendedor de ejemplo creados con éxito.');
    }
}
