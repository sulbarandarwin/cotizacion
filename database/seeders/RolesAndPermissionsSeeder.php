<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear Permisos
        // Permisos para Cotizaciones
        Permission::create(['name' => 'crear cotizaciones']);
        Permission::create(['name' => 'ver cotizaciones propias']); // Para vendedores
        Permission::create(['name' => 'ver todas las cotizaciones']); // Para admin
        Permission::create(['name' => 'editar cotizaciones']);
        Permission::create(['name' => 'eliminar cotizaciones']); // Solo Admin
        Permission::create(['name' => 'cambiar estado cotizaciones']);
        Permission::create(['name' => 'duplicar cotizaciones']);

        // Permisos para Productos
        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear productos']);
        Permission::create(['name' => 'editar productos']);
        Permission::create(['name' => 'eliminar productos']);
        Permission::create(['name' => 'importar productos']);
        Permission::create(['name' => 'exportar productos']);

        // Permisos para Clientes
        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear clientes']);
        Permission::create(['name' => 'editar clientes']);
        Permission::create(['name' => 'eliminar clientes']);
        Permission::create(['name' => 'importar clientes']);
        Permission::create(['name' => 'exportar clientes']);

        // Permisos para Usuarios (Vendedores)
        Permission::create(['name' => 'ver usuarios']);      // Admin ve a todos
        Permission::create(['name' => 'crear usuarios']);     // Admin crea vendedores
        Permission::create(['name' => 'editar usuarios']);    // Admin edita vendedores
        Permission::create(['name' => 'eliminar usuarios']);  // Admin elimina vendedores

        // Permisos para Configuración del Sistema
        Permission::create(['name' => 'gestionar configuracion']); // Solo Admin
        Permission::create(['name' => 'gestionar roles y permisos']); // Solo Admin (aunque este seeder lo hace)

        // Crear Roles y asignar Permisos existentes

        // Rol de Vendedor
        $vendedorRole = Role::create(['name' => 'Vendedor']);
        $vendedorRole->givePermissionTo([
            'crear cotizaciones',
            'ver cotizaciones propias', // Importante: Lógica extra en controlador para esto
            'editar cotizaciones',
            'cambiar estado cotizaciones',
            'duplicar cotizaciones',
            'ver productos',
            'exportar productos', // Quizás solo ver
            'ver clientes',
            'crear clientes',     // Un vendedor podría crear un nuevo cliente
            'editar clientes',    // Quizás solo los que él creó o se le asignen
            'exportar clientes',  // Quizás solo ver
        ]);

        // Rol de Administrador
        // Los administradores obtienen todos los permisos de forma automática o específica
        $adminRole = Role::create(['name' => 'Administrador']);
        // $adminRole->givePermissionTo(Permission::all()); // Opción 1: Dar todos los permisos existentes
        // Opción 2: Ser explícito (mejor para mantenimiento si añades más permisos después)
        $adminRole->givePermissionTo([
            'crear cotizaciones',
            'ver todas las cotizaciones',
            'editar cotizaciones',
            'eliminar cotizaciones',
            'cambiar estado cotizaciones',
            'duplicar cotizaciones',
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',
            'importar productos',
            'exportar productos',
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
            'importar clientes',
            'exportar clientes',
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'gestionar configuracion',
            'gestionar roles y permisos',
        ]);
    }
}
