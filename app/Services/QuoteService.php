<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteHistory;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuoteService
{
    public function generateQuoteNumber(bool $increment = true): string
    {
        $prefixSetting = SystemSetting::firstOrCreate(['key' => 'quote_prefix'], ['value' => 'COT-']);
        $nextNumberSetting = SystemSetting::firstOrCreate(['key' => 'next_quote_number'], ['value' => '1']);
        $prefix = $prefixSetting->value;
        $nextNumber = (int)$nextNumberSetting->value;
        $quoteNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        if ($increment) {
            $nextNumberSetting->value = (string)($nextNumber + 1);
            $nextNumberSetting->save();
        }
        return $quoteNumber;
    }

    public function createQuote(array $data, User $user): Quote
    {
        DB::beginTransaction();
        try {
            $quoteNumber = $this->generateQuoteNumber(true);
            $defaultProfit = (float)(SystemSetting::where('key', 'default_profit_percentage')->first()->value ?? 0.00);
            $defaultIvaRate = (float)(SystemSetting::where('key', 'iva_rate')->first()->value ?? 16.00);

            $quote = Quote::create([
                'quote_number' => $quoteNumber,
                'client_id' => $data['client_id'],
                'user_id' => $user->id,
                'issue_date' => Carbon::parse($data['issue_date']),
                'expiry_date' => Carbon::parse($data['expiry_date']),
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'notes_to_client' => $data['notes_to_client'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => isset($data['discount_value']) ? (float)str_replace(',', '.', $data['discount_value']) : 0,
                'tax_percentage' => isset($data['tax_percentage']) ? (float)str_replace(',', '.', $data['tax_percentage']) : $defaultIvaRate,
                'base_currency' => $data['base_currency'] ?? 'USD',
                'exchange_rate_bcv' => isset($data['exchange_rate_bcv']) && $data['exchange_rate_bcv'] !== '' ? (float)str_replace(',', '.', $data['exchange_rate_bcv']) : null,
                'exchange_rate_promedio' => isset($data['exchange_rate_promedio']) && $data['exchange_rate_promedio'] !== '' ? (float)str_replace(',', '.', $data['exchange_rate_promedio']) : null,
                'profit_percentage' => isset($data['profit_percentage']) ? (float)str_replace(',', '.', $data['profit_percentage']) : $defaultProfit,
                'status' => 'Borrador',
                'auto_save_data' => null,
            ]);

            $subtotal = 0;
            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $price = isset($itemData['price']) ? (float)str_replace(',', '.', $itemData['price']) : 0;
                    $quantity = isset($itemData['quantity']) ? (float)str_replace(',', '.', $itemData['quantity']) : 0;
                    $lineTotal = $quantity * $price;

                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'manual_product_name' => $itemData['manual_product_name'],
                        'manual_product_unit' => $itemData['manual_product_unit'] ?? 'Medida',
                        'quantity' => $quantity,
                        'cost' => isset($itemData['cost']) ? (float)str_replace(',', '.', $itemData['cost']) : 0,
                        'price' => $price,
                        'price_calculation_method' => $itemData['price_calculation_method'] ?? 'manual',
                        'applied_rate_value' => isset($itemData['applied_rate_value']) && $itemData['applied_rate_value'] !== '' ? (float)$itemData['applied_rate_value'] : null,
                        'line_total' => $lineTotal,
                        'estimated_delivery_time' => $itemData['estimated_delivery_time'] ?? null,
                        'cost_applies_iva' => !empty($itemData['cost_plus_iva']) && ($itemData['cost_plus_iva'] == '1' || $itemData['cost_plus_iva'] === true),
                    ]);
                    $subtotal += $lineTotal;
                }
            }

            $quote->subtotal = $subtotal;
            $discountAmount = 0;
            if (!empty($data['discount_type']) && isset($data['discount_value']) && (float)str_replace(',', '.', $data['discount_value']) > 0) {
                $cleanDiscountValue = (float)str_replace(',', '.', $data['discount_value']);
                if ($data['discount_type'] == 'percentage') {
                    $discountAmount = ($subtotal * $cleanDiscountValue) / 100;
                } else {
                    $discountAmount = $cleanDiscountValue;
                }
            }
            $quote->discount_amount = $discountAmount;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = ($taxableAmount * (float)$quote->tax_percentage) / 100;
            $quote->tax_amount = $taxAmount;
            $quote->total = $taxableAmount + $taxAmount;
            $quote->save();

            QuoteHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $user->id,
                'action' => 'Cotización Creada',
                'details' => ['numero_cotizacion' => $quote->quote_number]
            ]);

            DB::commit();
            return $quote;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en QuoteService@createQuote: " . $e->getMessage(), ['exception' => $e, 'data' => $data]);
            throw $e;
        }
    }

    public function updateQuote(Quote $quote, array $data, User $user): Quote
    {
        DB::beginTransaction();
        try {
            $defaultProfit = (float)(SystemSetting::where('key', 'default_profit_percentage')->first()->value ?? 0.00);
            $defaultIvaRate = (float)(SystemSetting::where('key', 'iva_rate')->first()->value ?? 16.00);

            $quote->fill([
                'client_id' => $data['client_id'],
                'issue_date' => Carbon::parse($data['issue_date']),
                'expiry_date' => Carbon::parse($data['expiry_date']),
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'notes_to_client' => $data['notes_to_client'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => isset($data['discount_value']) ? (float)str_replace(',', '.', $data['discount_value']) : 0,
                'tax_percentage' => isset($data['tax_percentage']) ? (float)str_replace(',', '.', $data['tax_percentage']) : $defaultIvaRate,
                'exchange_rate_bcv' => isset($data['exchange_rate_bcv']) && $data['exchange_rate_bcv'] !== '' ? (float)str_replace(',', '.', $data['exchange_rate_bcv']) : null,
                'exchange_rate_promedio' => isset($data['exchange_rate_promedio']) && $data['exchange_rate_promedio'] !== '' ? (float)str_replace(',', '.', $data['exchange_rate_promedio']) : null,
                'profit_percentage' => isset($data['profit_percentage']) ? (float)str_replace(',', '.', $data['profit_percentage']) : ($quote->profit_percentage ?? $defaultProfit),
                'auto_save_data' => null,
            ]);

            $subtotal = 0;
            $currentItemsIds = [];

            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $price = isset($itemData['price']) ? (float)str_replace(',', '.', $itemData['price']) : 0;
                    $quantity = isset($itemData['quantity']) ? (float)str_replace(',', '.', $itemData['quantity']) : 0;
                    $lineTotal = $quantity * $price;

                    $itemValues = [
                        'quote_id' => $quote->id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'manual_product_name' => $itemData['manual_product_name'],
                        'manual_product_unit' => $itemData['manual_product_unit'] ?? 'Medida',
                        'quantity' => $quantity,
                        'cost' => isset($itemData['cost']) ? (float)str_replace(',', '.', $itemData['cost']) : 0,
                        'price' => $price,
                        'price_calculation_method' => $itemData['price_calculation_method'] ?? 'manual',
                        'applied_rate_value' => isset($itemData['applied_rate_value']) && $itemData['applied_rate_value'] !== '' ? (float)$itemData['applied_rate_value'] : null,
                        'line_total' => $lineTotal,
                        'estimated_delivery_time' => $itemData['estimated_delivery_time'] ?? null,
                        'cost_applies_iva' => !empty($itemData['cost_plus_iva']) && ($itemData['cost_plus_iva'] == '1' || $itemData['cost_plus_iva'] === true),
                    ];

                    if (!empty($itemData['id'])) {
                        $item = QuoteItem::find($itemData['id']);
                        if ($item && $item->quote_id == $quote->id) {
                            $item->update($itemValues);
                            $currentItemsIds[] = $item->id;
                        }
                    } else {
                        $newItem = QuoteItem::create($itemValues);
                        $currentItemsIds[] = $newItem->id;
                    }
                    $subtotal += $lineTotal;
                }
            }
            
            if (isset($data['deleted_items']) && is_array($data['deleted_items'])) {
                 QuoteItem::where('quote_id', $quote->id)
                           ->whereIn('id', $data['deleted_items'])
                           ->delete();
            }
            $quote->items()->whereNotIn('id', $currentItemsIds)->delete();


            $quote->subtotal = $subtotal;
            $discountAmount = 0;
            if (!empty($data['discount_type']) && isset($data['discount_value']) && (float)str_replace(',', '.', $data['discount_value']) > 0) {
                $cleanDiscountValue = (float)str_replace(',', '.', $data['discount_value']);
                if ($data['discount_type'] == 'percentage') {
                    $discountAmount = ($subtotal * $cleanDiscountValue) / 100;
                } else {
                    $discountAmount = $cleanDiscountValue;
                }
            }
            $quote->discount_amount = $discountAmount;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = ($taxableAmount * (float)$quote->tax_percentage) / 100;
            $quote->tax_amount = $taxAmount;
            $quote->total = $taxableAmount + $taxAmount;
            $quote->save();

            QuoteHistory::create([
                'quote_id' => $quote->id,
                'user_id' => $user->id,
                'action' => 'Cotización Actualizada',
                'details' => ['numero_cotizacion' => $quote->quote_number]
            ]);

            DB::commit();
            return $quote;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en QuoteService@updateQuote para cotización #{$quote->id}: " . $e->getMessage(), ['exception' => $e, 'data' => $data]);
            throw $e;
        }
    }

    public function duplicateQuote(Quote $originalQuote, User $user): Quote
    {
        DB::beginTransaction();
        try {
            $defaultValidityDays = (int)(SystemSetting::where('key', 'default_validity_days')->first()->value ?? 15);
            $defaultProfitPercentage = (float)(SystemSetting::where('key', 'default_profit_percentage')->first()->value ?? 0.00);

            $newQuote = $originalQuote->replicate()->fill([
                'quote_number' => $this->generateQuoteNumber(true),
                'user_id' => $user->id,
                'status' => 'Borrador',
                'issue_date' => Carbon::now(),
                'expiry_date' => Carbon::now()->addDays($defaultValidityDays),
                'profit_percentage' => $originalQuote->profit_percentage ?? $defaultProfitPercentage,
                'auto_save_data' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $newQuote->save();

            if ($originalQuote->items()->exists()) {
                foreach ($originalQuote->items as $item) {
                    $newItemData = $item->replicate()->toArray();
                    unset($newItemData['id']);
                    // Asegurar que el estado del checkbox se duplica
                    $newItemData['cost_applies_iva'] = $item->cost_applies_iva; 
                    $newQuote->items()->create($newItemData);
                }
            }
            $newQuote->load('items'); // Recargar los ítems para el cálculo
            
            // Recalcular totales para la nueva cotización
            $subtotal = $newQuote->items->sum(function($item) {
                return (float)$item->quantity * (float)$item->price; 
            });
            $newQuote->subtotal = $subtotal;
            
            $discountAmount = 0;
            if ($newQuote->discount_type && (float)$newQuote->discount_value > 0) {
                if ($newQuote->discount_type == 'percentage') {
                    $discountAmount = ($subtotal * (float)$newQuote->discount_value) / 100;
                } else {
                    $discountAmount = (float)$newQuote->discount_value;
                }
            }
            $newQuote->discount_amount = $discountAmount;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = ($taxableAmount * (float)$newQuote->tax_percentage) / 100;
            $newQuote->tax_amount = $taxAmount;
            $newQuote->total = $taxableAmount + $taxAmount;
            $newQuote->save();

            QuoteHistory::create([
                'quote_id' => $newQuote->id,
                'user_id' => $user->id,
                'action' => 'Cotización Duplicada',
                'details' => ['duplicada_de' => $originalQuote->quote_number, 'nuevo_numero' => $newQuote->quote_number]
            ]);
            
            DB::commit();
            return $newQuote;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en QuoteService@duplicateQuote para cotización #{$originalQuote->id}: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
