<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Client;
use App\Models\User;
use App\Models\Product;
use App\Models\QuoteItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotes = Quote::with(['client', 'user'])->latest()->paginate(15);
        return view('admin.quotes.index', compact('quotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $iva_rate_setting = SystemSetting::where('key', 'iva_rate')->first();
        $bcv_rate_setting = SystemSetting::where('key', 'bcv_rate')->first();
        $promedio_rate_setting = SystemSetting::where('key', 'promedio_rate')->first();
        $validity_days_setting = SystemSetting::where('key', 'default_validity_days')->first();
        $terms_setting = SystemSetting::where('key', 'default_terms_conditions')->first();

        $iva_rate = $iva_rate_setting ? (float)$iva_rate_setting->value : 16.00;
        $bcv_rate = $bcv_rate_setting ? (float)$bcv_rate_setting->value : 0.00;
        $promedio_rate = $promedio_rate_setting ? (float)$promedio_rate_setting->value : 0.00;
        $default_validity_days = $validity_days_setting ? (int)$validity_days_setting->value : 15;
        $default_terms_conditions = $terms_setting ? $terms_setting->value : "1. Validez de la oferta: {$default_validity_days} días.\n2. Precios sujetos a cambio sin previo aviso.";

        return view('admin.quotes.create', compact('clients', 'iva_rate', 'bcv_rate', 'promedio_rate', 'default_validity_days', 'default_terms_conditions'));
    }

    /**
     * Genera el siguiente número de cotización.
     */
    protected function generateQuoteNumber(): string
    {
        $prefixSetting = SystemSetting::where('key', 'quote_prefix')->first();
        $nextNumberSetting = SystemSetting::where('key', 'next_quote_number')->first();

        $prefix = $prefixSetting ? $prefixSetting->value : 'COT-';
        $nextNumber = $nextNumberSetting ? (int)$nextNumberSetting->value : 1;

        $quoteNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        if ($nextNumberSetting) {
            $nextNumberSetting->value = (int)$nextNumberSetting->value + 1;
            $nextNumberSetting->save();
        } else {
             SystemSetting::updateOrCreate(['key' => 'next_quote_number'],['value' => $nextNumber + 1]);
        }
        if (!$prefixSetting) {
             SystemSetting::updateOrCreate(['key' => 'quote_prefix'],['value' => $prefix]);
        }
        return $quoteNumber;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'notes_to_client' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv',
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.min' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
            'items.*.quantity.required' => 'La cantidad es obligatoria para cada ítem.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a cero para cada ítem.',
            'items.*.cost.required' => 'El costo es obligatorio para cada ítem.',
            'items.*.price.required' => 'El precio es obligatorio para cada ítem.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('quotes.create')
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            $quoteNumber = $this->generateQuoteNumber();
            $ivaRateSetting = SystemSetting::where('key', 'iva_rate')->first();
            $ivaRate = $request->input('tax_percentage', $ivaRateSetting ? (float)$ivaRateSetting->value : 16.00);

            $quoteData = [
                'quote_number' => $quoteNumber,
                'client_id' => $request->client_id,
                'user_id' => Auth::id(),
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'terms_and_conditions' => $request->terms_and_conditions,
                'notes_to_client' => $request->notes_to_client,
                'internal_notes' => $request->internal_notes,
                'status' => 'Borrador',
                'base_currency' => 'USD',
                'tax_percentage' => $ivaRate,
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value ?? 0,
            ];

            $itemsDataFromRequest = $request->input('items', []);
            $calculatedSubtotal = 0;
            foreach ($itemsDataFromRequest as $item) {
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['price'] ?? 0);
                $calculatedSubtotal += $quantity * $price;
            }
            $quoteData['subtotal'] = $calculatedSubtotal;

            $discountValue = (float)($request->input('discount_value', 0));
            $discountType = $request->input('discount_type', 'fixed');
            $calculatedDiscountAmount = ($discountType === 'percentage' && $discountValue > 0) ? (($calculatedSubtotal * $discountValue) / 100) : $discountValue;
            $calculatedDiscountAmount = min($calculatedDiscountAmount, $calculatedSubtotal);
            $quoteData['discount_amount'] = $calculatedDiscountAmount;

            $taxableBase = $calculatedSubtotal - $calculatedDiscountAmount;
            $calculatedTaxAmount = ($taxableBase * $ivaRate) / 100;
            $quoteData['tax_amount'] = $calculatedTaxAmount;
            $quoteData['total'] = $taxableBase + $calculatedTaxAmount;

            $quote = Quote::create($quoteData);

            foreach ($itemsDataFromRequest as $itemInput) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'manual_product_name' => $itemInput['manual_product_name'],
                    'quantity' => $itemInput['quantity'],
                    'cost' => $itemInput['cost'],
                    'price_calculation_method' => $itemInput['price_calculation_method'] ?? null,
                    'price' => $itemInput['price'],
                    'line_total' => (float)($itemInput['quantity'] ?? 0) * (float)($itemInput['price'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$quote->quote_number} creada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear cotización: " . $e->getMessage() . "\nStack Trace:\n" . $e->getTraceAsString());
            return redirect()->route('quotes.create')
                        ->with('error', 'Error al crear la cotización: Verifique los datos e intente de nuevo. Detalles: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        $quote->load(['items', 'client', 'user']);
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $iva_rate_setting = SystemSetting::where('key', 'iva_rate')->first();
        $bcv_rate_setting = SystemSetting::where('key', 'bcv_rate')->first();
        $promedio_rate_setting = SystemSetting::where('key', 'promedio_rate')->first();
        $terms_setting = SystemSetting::where('key', 'default_terms_conditions')->first();

        $iva_rate_system = $iva_rate_setting ? (float)$iva_rate_setting->value : 16.00;
        $bcv_rate = $bcv_rate_setting ? (float)$bcv_rate_setting->value : 0.00;
        $promedio_rate = $promedio_rate_setting ? (float)$promedio_rate_setting->value : 0.00;
        $default_terms_conditions = $terms_setting ? $terms_setting->value : "";

        return view('admin.quotes.edit', [
            'quote' => $quote,
            'clients' => $clients,
            'iva_rate' => $quote->tax_percentage,
            'iva_rate_system' => $iva_rate_system,
            'bcv_rate' => $bcv_rate,
            'promedio_rate' => $promedio_rate,
            'default_terms_conditions' => $default_terms_conditions,
            'is_show_mode' => true
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote)
    {
        $quote->load(['items', 'client']);
        $clients = Client::orderBy('name')->pluck('name', 'id');

        $iva_rate_setting = SystemSetting::where('key', 'iva_rate')->first();
        $bcv_rate_setting = SystemSetting::where('key', 'bcv_rate')->first();
        $promedio_rate_setting = SystemSetting::where('key', 'promedio_rate')->first();
        $terms_setting = SystemSetting::where('key', 'default_terms_conditions')->first();

        $iva_rate_system = $iva_rate_setting ? (float)$iva_rate_setting->value : 16.00;
        $bcv_rate = $bcv_rate_setting ? (float)$bcv_rate_setting->value : 0.00;
        $promedio_rate = $promedio_rate_setting ? (float)$promedio_rate_setting->value : 0.00;
        $default_terms_conditions = $terms_setting ? $terms_setting->value : "1. Validez de la oferta: 15 días...";
        
        $is_show_mode = false;

        return view('admin.quotes.edit', compact(
            'quote',
            'clients',
            'iva_rate_system',
            'bcv_rate',
            'promedio_rate',
            'default_terms_conditions',
            'is_show_mode'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'notes_to_client' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.manual_product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.price_calculation_method' => 'nullable|in:promedio,bcv',
            'items.*.id' => 'nullable|integer|exists:quote_items,id,quote_id,' . $quote->id,
        ], [
            'items.required' => 'Debe agregar al menos un ítem a la cotización.',
            'items.*.manual_product_name.required' => 'El nombre del producto es obligatorio para cada ítem.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('quotes.edit', $quote->id)
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            $ivaRateSetting = SystemSetting::where('key', 'iva_rate')->first();
            $ivaRate = $request->input('tax_percentage', $ivaRateSetting ? (float)$ivaRateSetting->value : ($quote->tax_percentage ?? 16.00) );

            $quoteData = [
                'client_id' => $request->client_id,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'terms_and_conditions' => $request->terms_and_conditions,
                'notes_to_client' => $request->notes_to_client,
                'internal_notes' => $request->internal_notes,
                'tax_percentage' => $ivaRate,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value ?? 0,
            ];

            $itemsDataFromRequest = $request->input('items', []);
            $calculatedSubtotal = 0;
            foreach ($itemsDataFromRequest as $item) {
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['price'] ?? 0);
                $calculatedSubtotal += $quantity * $price;
            }
            $quoteData['subtotal'] = $calculatedSubtotal;

            $discountValue = (float)($request->input('discount_value', 0));
            $discountType = $request->input('discount_type', 'fixed');
            $calculatedDiscountAmount = ($discountType === 'percentage' && $discountValue > 0) ? (($calculatedSubtotal * $discountValue) / 100) : $discountValue;
            $calculatedDiscountAmount = min($calculatedDiscountAmount, $calculatedSubtotal);
            $quoteData['discount_amount'] = $calculatedDiscountAmount;

            $taxableBase = $calculatedSubtotal - $calculatedDiscountAmount;
            $calculatedTaxAmount = ($taxableBase * $ivaRate) / 100;
            $quoteData['tax_amount'] = $calculatedTaxAmount;
            $quoteData['total'] = $taxableBase + $calculatedTaxAmount;

            $quote->update($quoteData);

            $existingItemIds = $quote->items()->pluck('id')->toArray();
            $processedItemIds = [];
            $deletedItemsFromRequest = $request->input('deleted_items', []);

            foreach ($itemsDataFromRequest as $key => $itemInput) {
                $itemDataToSave = [
                    'quote_id' => $quote->id,
                    'manual_product_name' => $itemInput['manual_product_name'],
                    'quantity' => $itemInput['quantity'],
                    'cost' => $itemInput['cost'],
                    'price_calculation_method' => $itemInput['price_calculation_method'] ?? null,
                    'price' => $itemInput['price'],
                    'line_total' => (float)($itemInput['quantity'] ?? 0) * (float)($itemInput['price'] ?? 0),
                ];

                if (isset($itemInput['id']) && !empty($itemInput['id']) && in_array((int)$itemInput['id'], $existingItemIds)) {
                    $quoteItem = QuoteItem::find((int)$itemInput['id']);
                    if ($quoteItem) {
                        $quoteItem->update($itemDataToSave);
                        $processedItemIds[] = $quoteItem->id;
                    }
                } else {
                    $newQuoteItem = QuoteItem::create($itemDataToSave);
                    $processedItemIds[] = $newQuoteItem->id;
                }
            }

            $itemsToReallyDelete = array_diff($existingItemIds, $processedItemIds);
            if (!empty($deletedItemsFromRequest)) { // Priorizar los ítems marcados para borrar desde JS
                 $itemsToReallyDelete = array_unique(array_merge($itemsToReallyDelete, $deletedItemsFromRequest));
            }

            if (!empty($itemsToReallyDelete)) {
                QuoteItem::whereIn('id', $itemsToReallyDelete)->where('quote_id', $quote->id)->delete();
            }

            DB::commit();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$quote->quote_number} actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar cotización #{$quote->id}: " . $e->getMessage() . "\nStack Trace:\n" . $e->getTraceAsString());
            return redirect()->route('quotes.edit', $quote->id)
                        ->with('error', 'Error al actualizar la cotización: Verifique los datos. ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        // auth()->user()->can('eliminar_cotizaciones');
        try {
            $originalQuoteNumber = $quote->quote_number;
            // La relación onDelete('cascade') en la migración de quote_items
            // (definida en create_quote_items_table) debería encargarse de eliminar los ítems.
            // Si no tienes onDelete('cascade') en la llave foránea de quote_items.quote_id,
            // entonces necesitarías borrar los ítems manualmente primero:
            // $quote->items()->delete();
            $quote->delete();
            return redirect()->route('quotes.index')->with('success', "Cotización #{$originalQuoteNumber} eliminada exitosamente.");
        } catch (\Exception $e) {
            Log::error("Error al eliminar cotización #{$quote->id}: " . $e->getMessage());
            return redirect()->route('quotes.index')->with('error', 'Error al eliminar la cotización. Es posible que tenga registros asociados que impiden su eliminación si no se configuró la eliminación en cascada.');
        }
    }

    /**
     * Search for products (actualmente no se usa si la ruta está comentada).
     */
    public function searchProducts(Request $request)
    {
        $term = $request->input('term');
        if (empty($term)) {
            return response()->json([]);
        }
        $products = Product::where(function ($query) use ($term) {
                            $query->where('name', 'LIKE', "%{$term}%")
                                  ->orWhere('code', 'LIKE', "%{$term}%");
                        })
                        ->select('id', 'name', 'code', 'cost', 'unit_of_measure')
                        ->take(10)
                        ->get();
        return response()->json($products);
    }
}