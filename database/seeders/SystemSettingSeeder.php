<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Configuraciones de la Empresa
            ['key' => 'company_name', 'value' => 'Nombre de Tu Empresa S.A.'],
            ['key' => 'company_rif', 'value' => 'J-12345678-9'],
            ['key' => 'company_address', 'value' => 'Dirección Fiscal de Tu Empresa, Ciudad, Estado'],
            ['key' => 'company_phone', 'value' => '+58 261-7654321'],
            ['key' => 'company_email', 'value' => 'info@tuempresa.com'],
            ['key' => 'company_logo_path', 'value' => null], // Ruta al logo, se actualizará desde el panel

            // Configuraciones de Cotizaciones
            ['key' => 'quote_prefix', 'value' => 'COT-'], // Prefijo para los números de cotización
            ['key' => 'next_quote_number', 'value' => '1'],    // Siguiente número correlativo de cotización
            ['key' => 'default_terms_conditions', 'value' => "1. Precios sujetos a cambio sin previo aviso.\n2. Validez de la oferta: 15 días.\n3. Condiciones de pago: Contado."],
            ['key' => 'default_validity_days', 'value' => '15'], // Días de validez por defecto para una oferta

            // Tasas e Impuestos
            ['key' => 'iva_rate', 'value' => '16.00'],      // Tasa de IVA (ej: 16%)
            ['key' => 'bcv_rate', 'value' => '0.00'],       // Tasa BCV (se actualizará manualmente o por API)
            ['key' => 'promedio_rate', 'value' => '0.00'],  // Tasa Promedio (se actualizará manualmente)

            // Otras Configuraciones
            ['key' => 'default_currency', 'value' => 'USD'], // Moneda por defecto del sistema
            // Puedes añadir más configuraciones aquí según necesites
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }

        $this->command->info('Configuraciones del sistema cargadas exitosamente.');
    }
}
