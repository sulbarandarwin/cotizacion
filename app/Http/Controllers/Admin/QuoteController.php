<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\QuoteItem;
use App\Models\SystemSetting;
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
        $settings = SystemSetting::whereIn('key', [
            'iva_rate', 'bcv_rate', 'promedio_rate', 
            'default_validity_days', 'default_terms_conditions'
        ])->pluck('value', 'key');

        $iva_rate = (float) ($settings->get('iva_rate', 16.00));
        $bcv_rate = (float) ($settings->get('bcv_rate', 0.00));
        $promedio_rate = (float) ($settings->get('promedio_rate', 0.00));
        $default_validity_days = (int) ($settings->get('default_validity_days', 15));
        $default_terms_conditions = $settings->get('default_terms_conditions', "1. Validez de la oferta: {$default_validity_days} días.\n2. Precios sujetos a cambio sin previo aviso.");

        return view('admin.quotes.create', compact('clients', 'iva_rate', 'bcv_rate', 'promedio_rate', 'default_validity_days', 'default_terms_conditions'));
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
            'exchange_rate_bcv' => 'nullable|numeric|min:0',
            'exchange_rate_promedio' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv',
            'items.*.applied_rate_value' => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
            'items.*.price.required' => 'El precio del ítem es obligatorio.',
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
            return redirect()->route('quotes.create')
                        ->with('error', 'Error al crear la cotización: ' . $e->getMessage())
                        ->withInput();
        }
    }
    
    public function show(Quote $quote)
    {
        $quote->load(['items.product', 'client', 'user', 'history']);
        $companySettingsKeys = [
            'company_name', 'company_rif', 'company_address',
            'company_phone', 'company_email', 'company_logo',
            'company_nit', 'company_fax',
            'default_terms_conditions', 'default_payment_condition'
        ];
        $settings = SystemSetting::whereIn('key', $companySettingsKeys)->pluck('value', 'key');
        
        $companySettings = [];
        foreach ($companySettingsKeys as $key) {
            $companySettings[$key] = $settings->get($key);
        }
        
        $quote->payment_condition_display = $quote->payment_condition ?? ($companySettings['default_payment_condition'] ?? 'CONTADO');

        return view('admin.quotes.show', compact('quote', 'companySettings'));
    }

    public function edit(Quote $quote)
    {
        $quote->load(['items', 'client']);
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $settings = SystemSetting::whereIn('key', [
            'iva_rate', 'bcv_rate', 'promedio_rate', 
            'default_validity_days', 'default_terms_conditions'
        ])->pluck('value', 'key');

        $iva_rate_system = (float) ($settings->get('iva_rate', 16.00));
        $bcv_rate = (float) ($settings->get('bcv_rate', 0.00));
        $promedio_rate = (float) ($settings->get('promedio_rate', 0.00));
        $default_validity_days = (int) ($settings->get('default_validity_days', 15));
        
        $default_terms_conditions_system = $settings->get('default_terms_conditions', "1. Validez de la oferta: {$default_validity_days} días...");
        $default_terms_conditions_for_view = old('terms_and_conditions', $quote->terms_and_conditions ?? $default_terms_conditions_system);

        return view('admin.quotes.edit', compact(
            'quote',
            'clients',
            'iva_rate_system',
            'bcv_rate',
            'promedio_rate',
            'default_terms_conditions_for_view',
            'default_validity_days'
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
            'exchange_rate_bcv' => 'nullable|numeric|min:0',
            'exchange_rate_promedio' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:quote_items,id,quote_id,' . $quote->id,
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv',
            'items.*.applied_rate_value' => 'nullable|numeric|min:0',
            'deleted_items' => 'nullable|array',
            'deleted_items.*' => 'nullable|integer|exists:quote_items,id,quote_id,' . $quote->id,
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
            'items.*.price.required' => 'El precio del ítem es obligatorio.',
            'deleted_items.*.exists' => 'Uno de los ítems marcados para eliminar no es válido o no pertenece a esta cotización.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('quotes.edit', $quote->id)
                        ->withErrors($validator)
                        ->withInput();
        }

        // Puedes llamar a un método de servicio aquí si refactorizas 'update' también
        // $updatedQuote = $this->quoteService->updateQuote($quote, $validator->validated(), Auth::user());
        // Por ahora, mantenemos la lógica aquí para asegurar que la corrección de 'applied_rate_value' esté clara.
        DB::beginTransaction();
        try {
            $ivaRate = (float)$request->input('tax_percentage');

            $quoteInputData = $request->only([
                'client_id', 'issue_date', 'expiry_date',
                'terms_and_conditions', 'notes_to_client', 'internal_notes',
                'discount_type', 'exchange_rate_bcv', 'exchange_rate_promedio'
            ]);
            $quoteInputData['tax_percentage'] = $ivaRate;
            $quoteInputData['discount_value'] = $request->input('discount_value', 0);

            $itemsFromRequest = $request->input('items', []);
            $calculatedSubtotal = 0;
            foreach ($itemsFromRequest as $itemInput) {
                $quantity = (float)($itemInput['quantity'] ?? 0);
                $price = (float)($itemInput['price'] ?? 0);
                $calculatedSubtotal += $quantity * $price;
            }
            $quoteInputData['subtotal'] = $calculatedSubtotal;

            $discountValue = (float)($quoteInputData['discount_value']);
            $discountType = $quoteInputData['discount_type'];
            $calculatedDiscountAmount = 0;
            if ($discountValue > 0) {
                $calculatedDiscountAmount = ($discountType === 'percentage') ? (($calculatedSubtotal * $discountValue) / 100) : $discountValue;
                $calculatedDiscountAmount = min($calculatedDiscountAmount, $calculatedSubtotal);
            }
            $quoteInputData['discount_amount'] = $calculatedDiscountAmount;

            $taxableBase = $calculatedSubtotal - $calculatedDiscountAmount;
            $calculatedTaxAmount = ($taxableBase * $ivaRate) / 100;
            $quoteInputData['tax_amount'] = $calculatedTaxAmount;
            $quoteInputData['total'] = $taxableBase + $calculatedTaxAmount;

            $originalAttributes = $quote->getAttributes();
            $changesForHistory = [];

            foreach ($quoteInputData as $key => $newValue) {
                $originalValue = $originalAttributes[$key] ?? null;
                $areDifferent = false;
                if (in_array($key, ['issue_date', 'expiry_date'])) {
                    $originalDate = $originalValue ? Carbon::parse($originalValue)->toDateString() : null;
                    $newDate = $newValue ? Carbon::parse($newValue)->toDateString() : null;
                    if ($originalDate !== $newDate) $areDifferent = true;
                } elseif (is_numeric($originalValue) || is_numeric($newValue)) {
                    if ((string)(float)$originalValue !== (string)(float)$newValue) $areDifferent = true;
                } elseif ($originalValue !== $newValue) {
                    $areDifferent = true;
                }
                if ($areDifferent) {
                    $changesForHistory[$key] = ['anterior' => $originalValue, 'nuevo' => $newValue];
                }
            }

            $quote->update($quoteInputData);

            $existingItemIdsInDb = $quote->items()->pluck('id')->toArray();
            $processedItemIds = [];
            $itemsMarkedForDeletionByJs = [];
            if ($request->has('deleted_items') && is_array($request->input('deleted_items'))) {
                foreach ($request->input('deleted_items') as $delId) {
                    if (filter_var($delId, FILTER_VALIDATE_INT) !== false) {
                        $itemsMarkedForDeletionByJs[] = (int)$delId;
                    }
                }
            }

            foreach ($itemsFromRequest as $itemInput) {
                $itemData = [
                    'manual_product_name' => $itemInput['manual_product_name'],
                    'quantity' => (float)$itemInput['quantity'],
                    'cost' => (float)$itemInput['cost'],
                    'price_calculation_method' => $itemInput['price_calculation_method'] ?? null,
                    'applied_rate_value' => (isset($itemInput['applied_rate_value']) && $itemInput['applied_rate_value'] !== '' && is_numeric($itemInput['applied_rate_value']))
                                            ? (float)$itemInput['applied_rate_value']
                                            : null, // Asegura NULL si está vacío o no es numérico
                    'price' => (float)$itemInput['price'],
                    'line_total' => (float)($itemInput['quantity'] ?? 0) * (float)($itemInput['price'] ?? 0),
                ];

                if (isset($itemInput['id']) && !empty($itemInput['id']) && in_array((int)$itemInput['id'], $existingItemIdsInDb)) {
                    $quoteItem = QuoteItem::find((int)$itemInput['id']);
                    if ($quoteItem) {
                        $itemOriginalAttributes = $quoteItem->getAttributes();
                        $itemChanges = [];
                        foreach ($itemData as $itemKey => $itemValue) {
                            $originalItemValue = $itemOriginalAttributes[$itemKey] ?? null;
                             if (is_numeric($originalItemValue) || is_numeric($itemValue)) {
                                if ((string)(float)$originalItemValue !== (string)(float)$itemValue) {
                                    $itemChanges[$itemKey] = ['anterior' => $originalItemValue, 'nuevo' => $itemValue];
                                }
                            } elseif ($originalItemValue !== $itemValue) {
                                $itemChanges[$itemKey] = ['anterior' => $originalItemValue, 'nuevo' => $itemValue];
                            }
                        }
                        if (!empty($itemChanges)) {
                            $changesForHistory['item_actualizado_' . $quoteItem->id . '_' . Str::slug($itemInput['manual_product_name'] ?? 'item', '_')] = $itemChanges;
                        }
                        $quoteItem->update($itemData);
                        $processedItemIds[] = $quoteItem->id;
                    }
                } else {
                    $newQuoteItem = $quote->items()->create($itemData);
                    $processedItemIds[] = $newQuoteItem->id;
                    $changesForHistory['item_nuevo_' . $newQuoteItem->id . '_' . Str::slug($itemInput['manual_product_name'] ?? 'item', '_')] = $newQuoteItem->toArray();
                }
            }

            $itemsToDeleteIds = array_diff($existingItemIdsInDb, $processedItemIds);
            $itemsToDeleteIds = array_unique(array_merge($itemsToDeleteIds, $itemsMarkedForDeletionByJs));

            if (!empty($itemsToDeleteIds)) {
                $validItemsToDelete = [];
                foreach ($itemsToDeleteIds as $itemIdToDelete) {
                    $itemInstance = QuoteItem::where('id', $itemIdToDelete)->where('quote_id', $quote->id)->first();
                    if ($itemInstance) {
                        $validItemsToDelete[] = $itemIdToDelete;
                        $changesForHistory['item_eliminado_' . $itemIdToDelete . '_' . Str::slug($itemInstance->manual_product_name ?? 'item', '_')] = $itemInstance->toArray();
                    }
                }
                if(!empty($validItemsToDelete)){
                    QuoteItem::destroy($validItemsToDelete);
                }
            }

            if (!empty($changesForHistory)) {
                $quote->history()->create([
                    'user_id' => Auth::id(),
                    'action' => 'Cotización Actualizada',
                    'details' => $changesForHistory
                ]);
            }
            
            if ($quote->auto_save_data) {
                $quote->auto_save_data = null;
                $quote->saveQuietly();
            }

            DB::commit();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$quote->quote_number} actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("EXCEPCIÓN al actualizar cotización #{$quote->id}: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('quotes.edit', $quote->id)
                        ->with('error', 'Error al actualizar la cotización: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function destroy(Quote $quote)
    {
        DB::beginTransaction();
        try {
            $originalQuoteNumber = $quote->quote_number;
            $quote->delete(); 
            DB::commit();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$originalQuoteNumber} eliminada exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar cotización #{$quote->id}: " . $e->getMessage());
            return redirect()->route('quotes.index')->with('error', 'Error al eliminar la cotización.');
        }
    }

    public function duplicate(Quote $quote)
    {
        if (!$quote) {
            return redirect()->route('quotes.index')->with('error', 'Cotización original no encontrada.');
        }
        try {
            $newQuote = $this->quoteService->duplicateQuote($quote, Auth::user());
            return redirect()->route('quotes.edit', $newQuote->id)
                             ->with('success', "Cotización #{$quote->quote_number} duplicada exitosamente como #{$newQuote->quote_number}. Ahora puedes editarla.");
        } catch (\Exception $e) {
            return redirect()->route('quotes.show', $quote->id)
                             ->with('error', 'Error al duplicar la cotización: ' . $e->getMessage());
        }
    }

    public function downloadPDF(Quote $quote)
    {
        $quote->load(['items.product', 'client', 'user']);
        $companySettingsKeys = [
            'company_name', 'company_rif', 'company_address',
            'company_phone', 'company_email', 'company_logo',
            'company_nit', 'company_fax',
            'default_payment_condition'
        ];
        $settings = SystemSetting::whereIn('key', $companySettingsKeys)->pluck('value', 'key');
        
        $companySettings = [];
        foreach ($companySettingsKeys as $key) {
            $companySettings[$key] = $settings->get($key);
        }

        if (!empty($companySettings['company_logo'])) {
            $logoPath = public_path('storage/' . $companySettings['company_logo']);
            if (file_exists($logoPath)) {
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                if (!in_array(strtolower($logoType), ['svg', 'svgz'])) {
                    $logoData = file_get_contents($logoPath);
                    $companySettings['company_logo_base64'] = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
                }
            }
        }
        
        $quote->payment_condition_display = $quote->payment_condition ?? ($companySettings['default_payment_condition'] ?? 'CONTADO');

        $filename = 'presupuesto-' . Str::slug($quote->quote_number) . '.pdf';
        $pdf = PDF::loadView('admin.quotes.pdf', compact('quote', 'companySettings'));
        
        return $pdf->download($filename);
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

    public function autosave(Request $request, Quote $quote = null) // $quote puede ser null si se llama desde create sin ID
    {
        // Si $quote es null (por ejemplo, llamado desde create sin un ID),
        // el route model binding no lo resolverá, y $quote será null.
        // Si la ruta siempre incluye {quote}, entonces $quote siempre será una instancia o fallará antes.
        // El JS actual en edit.blade.php siempre pasa el ID, así que $quote debería estar presente.
        if (!$quote && $request->route('quote')) { // Intenta cargar si se pasó un ID pero el binding falló (poco probable con tipo Quote)
             $quote = Quote::find($request->route('quote'));
        }
        
        // Si $quote sigue siendo null y el autosave es para una cotización existente, hay un problema.
        if (!$quote && $request->has('id') && !empty($request->input('id'))){ // Si el JS envía un quote_id en el payload
            $quote = Quote::find($request->input('id'));
        }


        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|exists:clients,id',
            'items' => 'nullable|array',
            // No validar todos los campos aquí, ya que pueden estar incompletos durante el autoguardado
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!$quote) {
            // Lógica para manejar autoguardado de una cotización nueva (sin ID)
            // Esto es más complejo: ¿creamos un borrador temporal? ¿Guardamos en sesión?
            // Por ahora, devolvemos un error si no hay una cotización existente.
            return response()->json(['success' => false, 'message' => 'Autoguardado solo disponible para cotizaciones existentes o requiere guardar como borrador primero.'], 400);
        }

        try {
            $autosaveData = $request->except(['_token', '_method']);
            $quote->auto_save_data = $autosaveData;
            $quote->saveQuietly(); // saveQuietly para no disparar eventos de modelo/historial
            return response()->json(['success' => true, 'message' => 'Progreso guardado.', 'quote_id' => $quote->id]);
            
        } catch (\Exception $e) {
            Log::error("Error en autoguardado de cotización #{$quote->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al autoguardar el progreso.'], 500);
        }
    }
}