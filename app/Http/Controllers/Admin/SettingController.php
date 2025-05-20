<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    // Definir las claves esperadas para facilitar la carga y guardado
    protected $expectedSettingsKeys = [
        'company_name', 'company_rif', 'company_address', 'company_phone', 'company_email', 'company_logo',
        'company_nit', 'company_fax', 'iva_rate', 'bcv_rate', 'promedio_rate', 'default_profit_percentage',
        'default_validity_days', 'default_terms_conditions', 'default_payment_condition', 
        'payment_bank_details', 'payment_other_methods', 'tax_label',
        'quote_prefix', 'next_quote_number', // Aunque estos últimos son más para visualización
        'base_currency_symbol', 'secondary_currency_symbol'
    ];

    public function index()
    {
        $settingsArray = [];
        // Cargar solo las configuraciones esperadas o todas si se prefiere
        $dbSettings = SystemSetting::whereIn('key', $this->expectedSettingsKeys)->pluck('value', 'key');

        foreach ($this->expectedSettingsKeys as $key) {
            $settingsArray[$key] = $dbSettings->get($key);
        }

        // Asegurarse de que los valores por defecto se pasen si no están en la BD
        // (esto es mejor manejarlo en el seeder o al mostrar en la vista)

        return view('admin.settings.index', compact('settingsArray'));
    }

    public function update(Request $request)
    {
        // Validación básica (puedes expandirla)
        $validator = Validator::make($request->all(), [
            'company_name' => 'nullable|string|max:255',
            'company_rif' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Para el archivo del logo
            'iva_rate' => 'nullable|numeric|min:0|max:100',
            'bcv_rate' => 'nullable|numeric|min:0',
            'promedio_rate' => 'nullable|numeric|min:0',
            'default_profit_percentage' => 'nullable|numeric|min:0',
            'default_validity_days' => 'nullable|integer|min:0',
            'tax_label' => 'nullable|string|max:50',
            // Añade más validaciones según sea necesario
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.settings.index') // Asegúrate que el nombre de la ruta sea correcto
                        ->withErrors($validator)
                        ->withInput();
        }

        try {
            // Guardar configuraciones de texto/numéricas
            foreach ($request->except(['_token', '_method', 'company_logo_file']) as $key => $value) {
                if (in_array($key, $this->expectedSettingsKeys)) { // Solo guardar claves esperadas
                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value ?? ''] // Guardar string vacío si es null
                    );
                }
            }

            // Manejar subida de logo
            if ($request->hasFile('company_logo_file')) {
                $file = $request->file('company_logo_file');
                $filename = 'company_logo.' . $file->getClientOriginalExtension();

                // Eliminar logo anterior si existe
                $currentLogoPath = SystemSetting::where('key', 'company_logo')->value('value');
                if ($currentLogoPath && Storage::disk('public')->exists($currentLogoPath)) {
                    Storage::disk('public')->delete($currentLogoPath);
                }

                // Guardar nuevo logo
                $path = $file->storeAs('company', $filename, 'public'); // Guarda en storage/app/public/company

                SystemSetting::updateOrCreate(
                    ['key' => 'company_logo'],
                    ['value' => $path] // Guardar la ruta relativa
                );
            }

            // Limpiar caché de configuración para que los cambios se reflejen
            Artisan::call('config:clear');
            Artisan::call('cache:clear'); // Opcional, pero bueno para asegurar

            return redirect()->route('admin.settings.index')->with('success', 'Configuraciones actualizadas exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error al actualizar configuraciones: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.settings.index')->with('error', 'Error interno al actualizar las configuraciones.');
        }
    }
}