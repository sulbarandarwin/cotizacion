<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\SystemSetting;
use App\Models\User; // Necesario si pasas el objeto User
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Para Str::slug en el historial si es necesario

class QuoteService
{
    /**
     * Generates the next quote number.
     * Este método es ahora parte del servicio.
     */
    public function generateQuoteNumber(): string
    {
        $prefixSetting = SystemSetting::where('key', 'quote_prefix')->first();
        $nextNumberSetting = SystemSetting::where('key', 'next_quote_number')->first();

        $prefix = $prefixSetting ? $prefixSetting->value : 'COT-';
        $nextNumber = $nextNumberSetting ? (int)$nextNumberSetting->value : 1;

        $quoteNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        SystemSetting::updateOrCreate(
            ['key' => 'next_quote_number'],
            ['value' => (string)($nextNumber + 1)]
        );
        if (!$prefixSetting) {
             SystemSetting::updateOrCreate(['key' => 'quote_prefix'],['value' => $prefix]);
        }
        return $quoteNumber;
    }

    /**
     * Creates a new quote with its items and history.
     *
     * @param array $validatedData Los datos validados del request.
     * @param User $user El usuario autenticado que crea la cotización.
     * @return Quote La cotización creada.
     * @throws \Exception Si ocurre un error durante la creación.
     */
    public function createQuote(array $validatedData, User $user): Quote
    {
        DB::beginTransaction();
        try {
            $quoteNumber = $this->generateQuoteNumber();
            $ivaRate = (float)($validatedData['tax_percentage'] ?? SystemSetting::where('key', 'iva_rate')->first()->value ?? 16.00);

            $quoteInputData = [
                'client_id' => $validatedData['client_id'],
                'issue_date' => $validatedData['issue_date'],
                'expiry_date' => $validatedData['expiry_date'],
                'terms_and_conditions' => $validatedData['terms_and_conditions'] ?? null,
                'notes_to_client' => $validatedData['notes_to_client'] ?? null,
                'internal_notes' => $validatedData['internal_notes'] ?? null,
                'discount_type' => $validatedData['discount_type'] ?? null,
                'base_currency' => $validatedData['base_currency'],
                'exchange_rate_bcv' => $validatedData['exchange_rate_bcv'] ?? null,
                'exchange_rate_promedio' => $validatedData['exchange_rate_promedio'] ?? null,
                'quote_number' => $quoteNumber,
                'user_id' => $user->id,
                'status' => 'Borrador',
                'tax_percentage' => $ivaRate,
                'discount_value' => (float)($validatedData['discount_value'] ?? 0),
            ];

            $itemsFromRequest = $validatedData['items'] ?? [];
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

            $quote = Quote::create($quoteInputData);

            foreach ($itemsFromRequest as $itemInput) {
                $itemData = [
                    'manual_product_name' => $itemInput['manual_product_name'],
                    'quantity' => (float)$itemInput['quantity'],
                    'cost' => (float)$itemInput['cost'],
                    'price_calculation_method' => $itemInput['price_calculation_method'] ?? null,
                    'applied_rate_value' => (isset($itemInput['applied_rate_value']) && $itemInput['applied_rate_value'] !== '' && is_numeric($itemInput['applied_rate_value']))
                                            ? (float)$itemInput['applied_rate_value']
                                            : null,
                    'price' => (float)$itemInput['price'],
                    'line_total' => (float)($itemInput['quantity'] ?? 0) * (float)($itemInput['price'] ?? 0),
                    // 'estimated_delivery_time' => $itemInput['estimated_delivery_time'] ?? null,
                ];
                $quote->items()->create($itemData);
            }
            
            $quote->history()->create([
                'user_id' => $user->id,
                'action' => 'Cotización Creada',
                'details' => ['status_anterior' => null, 'status_nuevo' => 'Borrador']
            ]);

            DB::commit();
            return $quote;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en QuoteService@createQuote: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Relanzar la excepción para que el controlador pueda manejarla si es necesario,
            // o devolver null/false y manejarlo en el controlador.
            // Por simplicidad, relanzamos para que el controlador muestre un error genérico.
            throw $e; 
        }
    }

    // Aquí podrías añadir más métodos como updateQuote, duplicateQuote, etc.
    // Por ejemplo, para duplicateQuote:
    public function duplicateQuote(Quote $originalQuote, User $user): Quote
    {
        DB::beginTransaction();
        try {
            $defaultValidityDaysSetting = SystemSetting::where('key', 'default_validity_days')->first();
            $validityDays = $defaultValidityDaysSetting ? (int)$defaultValidityDaysSetting->value : 15;

            $newQuoteNumber = $this->generateQuoteNumber(); // Usa el método del servicio

            $newQuote = $originalQuote->replicate()->fill([
                'quote_number' => $newQuoteNumber,
                'issue_date' => now(),
                'expiry_date' => now()->addDays($validityDays),
                'status' => 'Borrador',
                'auto_save_data' => null,
                'user_id' => $user->id, // Asignar el usuario que duplica
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newQuote->save();

            if ($originalQuote->items()->exists()) {
                foreach ($originalQuote->items as $item) {
                    $newItemData = $item->replicate()->toArray();
                    unset($newItemData['id']);
                    $newQuote->items()->create($newItemData);
                }
            }

            $newQuote->history()->create([
                'user_id' => $user->id,
                'action' => 'Cotización Duplicada',
                'details' => ['duplicada_de' => $originalQuote->quote_number, 'nuevo_numero' => $newQuote->quote_number]
            ]);
            
            $originalQuote->history()->create([
                'user_id' => $user->id,
                'action' => 'Cotización Duplicada Hacia Otra',
                'details' => ['duplicada_a' => $newQuote->quote_number]
            ]);

            DB::commit();
            return $newQuote;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en QuoteService@duplicateQuote para cotización #{$originalQuote->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}