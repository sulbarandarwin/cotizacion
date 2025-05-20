<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos para Cotizaciones
        Permission::updateOrCreate(['name' => 'crear cotizaciones', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'ver cotizaciones propias', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'ver todas las cotizaciones', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'editar cotizaciones', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'eliminar cotizaciones', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'cambiar estado cotizaciones', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'duplicar cotizaciones', 'guard_name' => 'web']);

        // Permisos para Productos
        Permission::updateOrCreate(['name' => 'ver productos', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'crear productos', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'editar productos', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'eliminar productos', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'importar productos', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'exportar productos', 'guard_name' => 'web']); // <--- ASEGURAR QUE ESTÉ

        // Permisos para Clientes
        Permission::updateOrCreate(['name' => 'ver clientes', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'crear clientes', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'editar clientes', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'eliminar clientes', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'importar clientes', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'exportar clientes', 'guard_name' => 'web']);

        // Permisos para Administración de Usuarios y Roles
        Permission::updateOrCreate(['name' => 'ver usuarios', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'crear usuarios', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'editar usuarios', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'eliminar usuarios', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'gestionar roles y permisos', 'guard_name' => 'web']);

        // Permiso para Configuración del Sistema
        $manageSettingsPermission = Permission::updateOrCreate(['name' => 'manage_settings', 'guard_name' => 'web']);

        // Crear Roles
        $adminRole = Role::updateOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $vendedorRole = Role::updateOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);

        // Asignar Permisos al Rol de Vendedor
        $vendedorRole->syncPermissions([
            'crear cotizaciones',
            'ver cotizaciones propias',
            'editar cotizaciones',
            'cambiar estado cotizaciones',
            'duplicar cotizaciones',
            'ver productos',
            'ver clientes',
            'crear clientes',
            'editar clientes',
        ]);

        // Asignar Permisos al Rol de Administrador
        $adminPermissions = [
            'crear cotizaciones', 'ver todas las cotizaciones', 'editar cotizaciones',
            'eliminar cotizaciones', 'cambiar estado cotizaciones', 'duplicar cotizaciones',
            'ver productos', 'crear productos', 'editar productos', 'eliminar productos',
            'importar productos', 'exportar productos', // <--- ASEGURAR QUE ESTÉ
            'ver clientes', 'crear clientes', 'editar clientes', 'eliminar clientes',
            'importar clientes', 'exportar clientes',
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios',
            'gestionar roles y permisos',
            $manageSettingsPermission // o 'manage_settings' directamente si ya se creó
        ];
        $adminRole->syncPermissions($adminPermissions);
    }
}
