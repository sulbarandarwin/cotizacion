<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\QuoteHistory;
use App\Models\SystemSetting;
use App\Services\QuoteService; // Asegúrate que tu QuoteService.php esté correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function index()
    {
        $quotes = Quote::with(['client', 'user'])->latest()->paginate(15);
        return view('admin.quotes.index', compact('quotes'));
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para crear una cotización.');
        }
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $iva_rate_setting = SystemSetting::where('key', 'iva_rate')->first();
        $iva_rate = $iva_rate_setting ? (float) $iva_rate_setting->value : 16.00;

        $bcv_rate_setting = SystemSetting::where('key', 'bcv_rate')->first();
        $bcv_rate = $bcv_rate_setting ? (float) $bcv_rate_setting->value : 0.00;

        $promedio_rate_setting = SystemSetting::where('key', 'promedio_rate')->first();
        $promedio_rate = $promedio_rate_setting ? (float) $promedio_rate_setting->value : 0.00;
        
        $default_profit_percentage_setting = SystemSetting::where('key', 'default_profit_percentage')->first();
        $default_profit_percentage = $default_profit_percentage_setting ? (float) $default_profit_percentage_setting->value : 20.00;

        $default_validity_days_setting = SystemSetting::where('key', 'default_validity_days')->first();
        $default_validity_days = $default_validity_days_setting ? (int) $default_validity_days_setting->value : 15;
        
        $default_terms_conditions_setting = SystemSetting::where('key', 'default_terms_conditions')->first();
        $default_terms_conditions = $default_terms_conditions_setting ? $default_terms_conditions_setting->value : "1. Validez de la oferta: {$default_validity_days} días.\n2. Precios sujetos a cambio sin previo aviso.";

        $nextQuoteNumber = $this->quoteService->generateQuoteNumber(false);

        return view('admin.quotes.create', compact('clients', 'iva_rate', 'bcv_rate', 'promedio_rate', 'default_profit_percentage', 'default_validity_days', 'default_terms_conditions', 'nextQuoteNumber'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para guardar la cotización.');
        }
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'terms_and_conditions' => 'nullable|string|max:5000',
            'notes_to_client' => 'nullable|string|max:5000',
            'internal_notes' => 'nullable|string|max:5000',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'base_currency' => 'required|string|in:USD,BS',
            'exchange_rate_bcv' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/',
            'exchange_rate_promedio' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/',
            'profit_percentage' => 'nullable|numeric|min:0|max:1000|regex:/^\d*(\.\d{1,2})?$/',
            'items' => 'required|array|min:1',
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv,manual',
            'items.*.applied_rate_value' => 'nullable|numeric|min:0',
            'items.*.estimated_delivery_time' => 'nullable|string|max:255',
            'items.*.manual_product_unit' => 'nullable|string|max:100',
            'items.*.cost_applies_iva' => 'nullable|boolean',
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
            'profit_percentage.numeric' => 'El porcentaje de utilidad debe ser un número.',
            'profit_percentage.min' => 'El porcentaje de utilidad no puede ser negativo.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('quotes.create')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $quote = $this->quoteService->createQuote($validator->validated(), Auth::user());
            return redirect()->route('quotes.index')->with('success', "Cotización #{$quote->quote_number} creada exitosamente.");
        } catch (\Exception $e) {
            Log::error("Error al crear cotización por usuario ID {$userId}: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('quotes.create')
                ->with('error', 'Error interno al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mantenemos Route Model Binding para show, edit, update si funcionan bien
    public function show(Quote $quote)
    {
        if (!$quote || !$quote->exists) {
            return redirect()->route('quotes.index')->with('error', 'Cotización no encontrada.');
        }
        $quote->load(['items.product', 'client', 'user', 'history.user']);
        // ... (resto del método show como estaba) ...
        $companySettingsKeys = [
            'company_name', 'company_rif', 'company_address',
            'company_phone', 'company_email', 'company_logo',
            'company_nit', 'company_fax',
            'default_terms_conditions', 'default_payment_condition',
            'payment_bank_details', 'payment_other_methods', 'tax_label'
        ];
        $settingsCollection = SystemSetting::whereIn('key', $companySettingsKeys)->pluck('value', 'key');
        $companySettings = [];
        foreach ($companySettingsKeys as $key) {
            $companySettings[$key] = $settingsCollection->get($key);
        }
        $companySettings['company_logo_base64'] = null;
        if (!empty($companySettings['company_logo'])) {
            $logoPath = storage_path('app/public/' . $companySettings['company_logo']);
            if (file_exists($logoPath)) {
                try {
                    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $logoData = file_get_contents($logoPath);
                    $companySettings['company_logo_base64'] = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    Log::error("Error al procesar logo para PDF/Show: " . $e->getMessage());
                }
            }
        }
        return view('admin.quotes.show', compact('quote', 'companySettings'));
    }

    public function edit(Quote $quote)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para editar una cotización.');
        }
        if (!$quote || !$quote->exists) {
            return redirect()->route('quotes.index')->with('error', 'Cotización no encontrada para editar.');
        }
        $quote->load('items.product');
        // ... (resto del método edit como estaba) ...
        $clients = Client::orderBy('name')->pluck('name', 'id');
        $iva_rate_setting = SystemSetting::where('key', 'iva_rate')->first();
        $iva_rate_system = $iva_rate_setting ? (float) $iva_rate_setting->value : 16.00;
        $bcv_rate_setting = SystemSetting::where('key', 'bcv_rate')->first();
        $current_bcv_rate = $quote->exchange_rate_bcv ?? ($bcv_rate_setting ? (float) $bcv_rate_setting->value : 0.00);
        $promedio_rate_setting = SystemSetting::where('key', 'promedio_rate')->first();
        $current_promedio_rate = $quote->exchange_rate_promedio ?? ($promedio_rate_setting ? (float) $promedio_rate_setting->value : 0.00);
        $default_profit_percentage_setting = SystemSetting::where('key', 'default_profit_percentage')->first();
        $current_profit_percentage = $quote->profit_percentage ?? ($default_profit_percentage_setting ? (float) $default_profit_percentage_setting->value : 20.00);
        $default_validity_days_setting = SystemSetting::where('key', 'default_validity_days')->first();
        $default_validity_days = $default_validity_days_setting ? (int) $default_validity_days_setting->value : 15;
        $default_terms_conditions_setting = SystemSetting::where('key', 'default_terms_conditions')->first();
        $default_terms_conditions = $default_terms_conditions_setting ? $default_terms_conditions_setting->value : "Validez: {$default_validity_days} días.";
        $current_terms_conditions = $quote->terms_and_conditions ?? $default_terms_conditions;
        return view('admin.quotes.edit', compact(
            'quote', 'clients', 'iva_rate_system',
            'current_bcv_rate', 'current_promedio_rate', 'current_profit_percentage',
            'current_terms_conditions', 'default_validity_days'
        ));
    }

    public function update(Request $request, Quote $quote)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para actualizar la cotización.');
        }
        $userId = Auth::id();
        if (!$quote || !$quote->exists) {
            return redirect()->route('quotes.index')->with('error', 'Cotización no encontrada para actualizar.');
        }
        // ... (resto del método update como estaba, validaciones, etc.) ...
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'terms_and_conditions' => 'nullable|string|max:5000',
            'notes_to_client' => 'nullable|string|max:5000',
            'internal_notes' => 'nullable|string|max:5000',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percentage' => 'required|numeric|min:0',
            'exchange_rate_bcv' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/',
            'exchange_rate_promedio' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/',
            'profit_percentage' => 'nullable|numeric|min:0|max:1000|regex:/^\d*(\.\d{1,2})?$/',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:quote_items,id,quote_id,' . $quote->id,
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv,manual',
            'items.*.applied_rate_value' => 'nullable|numeric|min:0',
            'items.*.estimated_delivery_time' => 'nullable|string|max:255',
            'items.*.manual_product_unit' => 'nullable|string|max:100',
            'items.*.cost_applies_iva' => 'nullable|boolean',
            'deleted_items' => 'nullable|array',
            'deleted_items.*' => 'nullable|integer|exists:quote_items,id,quote_id,' . $quote->id,
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
             'profit_percentage.numeric' => 'El porcentaje de utilidad debe ser un número.',
            'profit_percentage.min' => 'El porcentaje de utilidad no puede ser negativo.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('quotes.edit', $quote->id)
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $this->quoteService->updateQuote($quote, $validator->validated(), Auth::user());
            return redirect()->route('quotes.show', $quote->id)->with('success', "Cotización #{$quote->quote_number} actualizada exitosamente.");
        } catch (\Exception $e) {
            Log::error("Error al actualizar cotización #{$quote->id} por usuario ID {$userId}: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('quotes.edit', $quote->id)
                ->with('error', 'Error interno al actualizar la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    // MÉTODO DESTROY MODIFICADO PARA RECIBIR ID DIRECTAMENTE
    public function destroy($quoteId) 
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar esta acción.');
        }
        $userId = Auth::id();
        
        // Log para ver el ID que llega
        Log::info("Intento de eliminar cotización. ID recibido del router: " . $quoteId . " por Usuario ID: " . $userId);

        $quote = Quote::find($quoteId); // Buscar la cotización manualmente

        if (!$quote) { // Si Quote::find devuelve null
             Log::error("DESTROY: Cotización no encontrada con ID: {$quoteId} por usuario ID: " . $userId);
             return redirect()->route('quotes.index')->with('error', "Error al eliminar: Cotización con ID {$quoteId} no encontrada.");
        }

        // Si se encontró la cotización, proceder a eliminar
        DB::beginTransaction();
        try {
            $quoteNumber = $quote->quote_number;
            $clientName = $quote->client ? $quote->client->name : 'N/A';

            QuoteHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $userId,
                'action' => 'Cotización Eliminada',
                'details' => ['quote_number' => $quoteNumber, 'client' => $clientName]
            ]);
            $quote->items()->delete();
            $quote->delete();
            DB::commit();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$quoteNumber} eliminada exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar cotización ID {$quote->id} por usuario ID {$userId}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('quotes.index')->with('error', 'Error interno al eliminar la cotización.');
        }
    }

    // MÉTODO DUPLICATE MODIFICADO PARA RECIBIR ID DIRECTAMENTE
    public function duplicate($quoteId) 
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar esta acción.');
        }
        $userId = Auth::id();

        // Log para ver el ID que llega
        Log::info("Intento de duplicar cotización. ID recibido del router: " . $quoteId . " por Usuario ID: " . $userId);

        $originalQuote = Quote::find($quoteId); // Buscar la cotización original manualmente

        if (!$originalQuote) { // Si no se encuentra
             Log::error("DUPLICATE: Cotización original no encontrada con ID: {$quoteId} por usuario ID: " . $userId);
             return redirect()->route('quotes.index')->with('error', "Error al duplicar: Cotización original con ID {$quoteId} no encontrada.");
        }

        // Si se encontró la cotización original, proceder
        $originalQuote->loadMissing('items'); // Cargar ítems si no están cargados

        DB::beginTransaction();
        try {
            // Usar el método duplicateQuote de QuoteService
            $newQuote = $this->quoteService->duplicateQuote($originalQuote, Auth::user());
            
            DB::commit();
            return redirect()->route('quotes.edit', $newQuote->id)->with('success', "Cotización #{$originalQuote->quote_number} duplicada. Nuevo borrador #{$newQuote->quote_number} listo para editar.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al duplicar cotización (ID original: {$originalQuote->id}): " . $e->getMessage() . " por usuario ID: " . $userId, ['trace' => $e->getTraceAsString()]);
            return redirect()->route('quotes.index')->with('error', 'Error interno al duplicar la cotización: '. $e->getMessage());
        }
    }

    public function downloadPDF(Quote $quote)
    {
        if (!$quote || !$quote->exists) {
             return redirect()->route('quotes.index')->with('error', 'Error al descargar PDF: Cotización no encontrada.');
        }
        // ... (resto del método downloadPDF como estaba) ...
        $quote->load(['items.product', 'client', 'user']);
        $companySettingsKeys = [
            'company_name', 'company_rif', 'company_address',
            'company_phone', 'company_email', 'company_logo',
            'company_nit', 'company_fax', 'default_payment_condition',
            'payment_bank_details',
            'payment_other_methods',
            'tax_label'
        ];
        $settingsCollection = SystemSetting::whereIn('key', $companySettingsKeys)->pluck('value', 'key');
        $companySettings = [];
        foreach ($companySettingsKeys as $key) {
            $companySettings[$key] = $settingsCollection->get($key);
        }
        $companySettings['company_logo_base64'] = null;
        if (!empty($companySettings['company_logo'])) {
            $logoPath = storage_path('app/public/' . $companySettings['company_logo']);
            if (file_exists($logoPath)) {
                try {
                    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $logoData = file_get_contents($logoPath);
                    $companySettings['company_logo_base64'] = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
                } catch (\Exception $e) {
                    Log::error("Error al procesar logo para PDF: " . $e->getMessage());
                }
            } else {
                 Log::warning("Logo no encontrado en la ruta para PDF: " . $logoPath);
            }
        }
        $filename = 'presupuesto-' . Str::slug($quote->quote_number) . '-' . Carbon::now()->format('YmdHis') . '.pdf';
        try {
            $pdf = Pdf::loadView('admin.quotes.pdf', compact('quote', 'companySettings'));
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Error crítico al generar PDF para cotización #{$quote->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'No se pudo generar el PDF. Revise los logs.');
        }
    }

    public function searchProducts(Request $request)
    {
        // ... (método searchProducts como estaba) ...
        $term = $request->input('term');
        if (empty($term)) {
            return response()->json([]);
        }
        $products = \App\Models\Product::where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('code', 'LIKE', "%{$term}%");
        })
        ->select('id', 'name', 'code', 'cost', 'unit_of_measure', 'tax_type')
        ->take(10)
        ->get();
        return response()->json($products);
    }

    public function autosave(Request $request, Quote $quote = null)
    {
        // ... (método autosave como estaba, con la verificación de $quote->exists) ...
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Debe iniciar sesión para autoguardar.', 'redirect' => route('login')], 401);
        }
        try {
            $dataToSave = $request->except(['_token', '_method', 'clear_autosave']);
            if ($request->has('clear_autosave') && $quote && $quote->exists) {
                 $quote->auto_save_data = null;
                 $quote->saveQuietly();
                 return response()->json(['success' => true, 'message' => 'Datos de autoguardado limpiados.']);
            }
            if ($quote && $quote->exists) {
                $quote->auto_save_data = $dataToSave;
                $quote->saveQuietly();
                return response()->json(['success' => true, 'message' => 'Progreso guardado.', 'quote_id' => $quote->id]);
            } else {
                 return response()->json(['success' => false, 'message' => 'Autoguardado requiere una cotización existente. Guarde como borrador primero.']);
            }
        } catch (\Exception $e) {
            Log::error('Error en autosave: ' . $e->getMessage(), ['data' => $request->all(), 'quote_id' => $quote?->id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al autoguardar.'], 500);
        }
    }

    public function changeStatus(Request $request, Quote $quote) // Route Model Binding
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar esta acción.');
        }
        $userId = Auth::id();
         if (!$quote || !$quote->exists) {
             Log::error("Intento de cambiar estado de cotización no encontrada (ID: ".($quote ? $quote->id : 'desconocido').") por usuario ID: " . $userId);
             return redirect()->back()->with('error', 'Error: Cotización no encontrada o ID inválido.');
        }
        // ... (resto del método changeStatus como estaba) ...
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', Quote::getAllStatuses()),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Estado no válido seleccionado.');
        }
        $oldStatus = $quote->status;
        $newStatus = $request->input('status');
        if ($oldStatus === $newStatus) {
            return redirect()->back()->with('info', 'La cotización ya tiene este estado.');
        }
        DB::beginTransaction();
        try {
            $quote->status = $newStatus;
            $quote->save();
            QuoteHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $userId,
                'action' => "Estado Cambiado: {$oldStatus} -> {$newStatus}",
                'details' => [
                    'status_anterior' => $oldStatus,
                    'status_nuevo' => $newStatus,
                ]
            ]);
            DB::commit();
            return redirect()->back()->with('success', "Estado de la cotización #{$quote->quote_number} cambiado a '{$newStatus}'.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cambiar estado de cotización #{$quote->id} por usuario ID {$userId}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error al cambiar el estado de la cotización.');
        }
    }
}
