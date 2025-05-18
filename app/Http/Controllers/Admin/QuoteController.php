<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\QuoteHistory;
use App\Models\SystemSetting; // Asegúrate que esta línea esté presente
use App\Services\QuoteService;
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
        $clients = Client::orderBy('name')->pluck('name', 'id');
        $settingsKeys = [
            'iva_rate', 'bcv_rate', 'promedio_rate',
            'default_validity_days', 'default_terms_conditions',
            'default_profit_percentage' // NUEVA CLAVE A CARGAR
        ];
        $settings = SystemSetting::whereIn('key', $settingsKeys)->pluck('value', 'key');

        $iva_rate = (float) ($settings->get('iva_rate', 16.00));
        $bcv_rate = (float) ($settings->get('bcv_rate', 0.00));
        $promedio_rate = (float) ($settings->get('promedio_rate', 0.00));
        $default_profit_percentage = (float) ($settings->get('default_profit_percentage', 20.00)); // VALOR POR DEFECTO
        $default_validity_days = (int) ($settings->get('default_validity_days', 15));
        $default_terms_conditions = $settings->get('default_terms_conditions', "1. Validez de la oferta: {$default_validity_days} días.\n2. Precios sujetos a cambio sin previo aviso.");

        $nextQuoteNumber = $this->quoteService->generateQuoteNumber(false);

        return view('admin.quotes.create', compact('clients', 'iva_rate', 'bcv_rate', 'promedio_rate', 'default_profit_percentage', 'default_validity_days', 'default_terms_conditions', 'nextQuoteNumber'));
    }

    public function store(Request $request)
    {
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
            'exchange_rate_bcv' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/', // Ajustado para hasta 4 decimales
            'exchange_rate_promedio' => 'nullable|numeric|min:0|regex:/^\d*(\.\d{1,4})?$/', // Ajustado para hasta 4 decimales
            'profit_percentage' => 'nullable|numeric|min:0|max:1000|regex:/^\d*(\.\d{1,2})?$/', // NUEVA VALIDACIÓN
            'items' => 'required|array|min:1',
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price' => 'required|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv,manual',
            'items.*.applied_rate_value' => 'nullable|numeric|min:0',
            'items.*.estimated_delivery_time' => 'nullable|string|max:255',
            'items.*.manual_product_unit' => 'nullable|string|max:100',
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
            Log::error("Error al crear cotización: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine(), $e->getTrace());
            return redirect()->route('quotes.create')
                ->with('error', 'Error interno al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Quote $quote)
    {
        $quote->load(['items.product', 'client', 'user', 'history.user']);

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
        $quote->load('items.product');
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $settingsKeys = [
            'iva_rate', 'bcv_rate', 'promedio_rate',
            'default_validity_days', 'default_terms_conditions',
            'default_profit_percentage' // NUEVA CLAVE A CARGAR
        ];
        $settings = SystemSetting::whereIn('key', $settingsKeys)->pluck('value', 'key');

        $iva_rate_system = (float) ($settings->get('iva_rate', 16.00));
        $current_bcv_rate = $quote->exchange_rate_bcv ?? (float) ($settings->get('bcv_rate', 0.00));
        $current_promedio_rate = $quote->exchange_rate_promedio ?? (float) ($settings->get('promedio_rate', 0.00));
        // Para profit_percentage, tomar el de la cotización si existe, sino el default del sistema
        $current_profit_percentage = $quote->profit_percentage ?? (float)($settings->get('default_profit_percentage', 20.00));

        $default_validity_days = (int) ($settings->get('default_validity_days', 15));
        $current_terms_conditions = $quote->terms_and_conditions ?? $settings->get('default_terms_conditions', "Validez: {$default_validity_days} días.");

        return view('admin.quotes.edit', compact(
            'quote', 'clients', 'iva_rate_system',
            'current_bcv_rate', 'current_promedio_rate', 'current_profit_percentage',
            'current_terms_conditions', 'default_validity_days'
        ));
    }

    public function update(Request $request, Quote $quote)
    {
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
            'profit_percentage' => 'nullable|numeric|min:0|max:1000|regex:/^\d*(\.\d{1,2})?$/', // NUEVA VALIDACIÓN
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
            Log::error("Error al actualizar cotización #{$quote->id}: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine(), $e->getTrace());
             return redirect()->route('quotes.edit', $quote->id)
                ->with('error', 'Error interno al actualizar la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ... (destroy, duplicate, downloadPDF, searchProducts, autosave, changeStatus) ...
    // Estos métodos no necesitan cambios para esta edición específica del campo profit_percentage

    public function downloadPDF(Quote $quote)
    {
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
                 Log::warning("Logo no encontrado en la ruta: " . $logoPath);
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
            return redirect()->back()->with('error', 'No se pudo generar el PDF debido a un error interno. Por favor, revise los logs o contacte al administrador.');
        }
    }

    public function searchProducts(Request $request)
    {
        $term = $request->input('term');
        if (empty($term)) {
            return response()->json([]);
        }

        $products = \App\Models\Product::where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('code', 'LIKE', "%{$term}%");
        })
        ->select('id', 'name', 'code', 'cost', 'unit_of_measure')
        ->take(10)
        ->get();

        return response()->json($products);
    }

    public function autosave(Request $request, Quote $quote = null)
    {
        try {
            $dataToSave = $request->except(['_token', '_method', 'clear_autosave']);

            if ($request->has('clear_autosave') && $quote) {
                 $quote->auto_save_data = null;
                 $quote->saveQuietly();
                 return response()->json(['success' => true, 'message' => 'Datos de autoguardado limpiados.']);
            }

            if ($quote) {
                $quote->auto_save_data = $dataToSave;
                $quote->saveQuietly();
                return response()->json(['success' => true, 'message' => 'Progreso guardado.', 'quote_id' => $quote->id]);
            } else {
                return response()->json(['success' => false, 'message' => 'Autoguardado para cotización nueva requiere un ID. Guarde como borrador primero.']);
            }
        } catch (\Exception $e) {
            Log::error('Error en autosave: ' . $e->getMessage(), ['data' => $request->all(), 'quote_id' => $quote?->id]);
            return response()->json(['success' => false, 'message' => 'Error al autoguardar: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:Borrador,Enviada,Aceptada,Rechazada,Expirada,Cancelada',
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
                'user_id' => Auth::id(),
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
            Log::error("Error al cambiar estado de cotización #{$quote->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cambiar el estado de la cotización.');
        }
    }

}