<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate table para evitar duplicados en re-seed, o usa updateOrCreate
        DB::table('system_settings')->truncate(); 

        $settings = [
            // Datos de la Empresa
            ['key' => 'company_name', 'value' => 'Nombre de tu Empresa S.A.'],
            ['key' => 'company_rif', 'value' => 'J-12345678-9'],
            ['key' => 'company_address', 'value' => 'Calle Falsa 123, Ciudad, Estado, País'],
            ['key' => 'company_phone', 'value' => '+58 261 7777777'],
            ['key' => 'company_email', 'value' => 'info@tuempresa.com'],
            ['key' => 'company_logo', 'value' => null], // Ruta al logo, se actualizará desde el panel
            ['key' => 'company_nit', 'value' => null], // Si aplica
            ['key' => 'company_fax', 'value' => null], // Si aplica

            // Configuraciones de Cotización
            ['key' => 'iva_rate', 'value' => '16.00'],
            ['key' => 'bcv_rate', 'value' => '0.00'], // Tasa BCV (referencial)
            ['key' => 'promedio_rate', 'value' => '0.00'], // Tasa Promedio (referencial)
            ['key' => 'default_profit_percentage', 'value' => '20.00'],
            ['key' => 'default_validity_days', 'value' => '15'],
            ['key' => 'default_terms_conditions', 'value' => "1. Validez de la oferta: 15 días.\n2. Precios en Dólares (USD).\n3. Pago: Contado."],
            ['key' => 'default_payment_condition', 'value' => 'Contado'],
            ['key' => 'payment_bank_details', 'value' => "Banco Ejemplo\nCuenta Corriente Nro: 0100-0000-00-0000000000\nA nombre de: Nombre de tu Empresa S.A.\nRIF: J-12345678-9"],
            ['key' => 'payment_other_methods', 'value' => "Zelle: info@tuempresa.com\nPago Móvil: (0414) 1234567, CI: 12345678, Banco Ejemplo"],
            ['key' => 'tax_label', 'value' => 'IVA'], // Etiqueta para el impuesto en PDFs/Vistas

            // Números de Cotización (manejados por QuoteService, pero pueden tener un default aquí)
            ['key' => 'quote_prefix', 'value' => 'COT-'],
            ['key' => 'next_quote_number', 'value' => '1'], // QuoteService lo incrementará

            // Monedas
            ['key' => 'base_currency_symbol', 'value' => '$'],
            ['key' => 'secondary_currency_symbol', 'value' => 'Bs.'], // Si aplica
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}