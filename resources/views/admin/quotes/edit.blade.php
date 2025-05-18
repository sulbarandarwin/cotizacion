@extends('adminlte::page')

@section('title', 'Editar Cotización #' . $quote->quote_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Editar Cotización #{{ $quote->quote_number }}</h1>
        <a href="{{ route('quotes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a la Lista</a>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error de Validación!</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
            {{ session('error') }}
        </div>
    @endif
     @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif
     @if (session('info'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-info"></i> Información</h5>
            {{ session('info') }}
        </div>
    @endif

    <form id="editQuoteForm" action="{{ route('quotes.update', $quote) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" id="quote_id_for_autosave" value="{{ $quote->id }}">

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Información General y Cliente (Editando)</h3>
                <div class="card-tools">
                    <button type="button" id="autoSaveTriggerButton" class="btn btn-sm btn-outline-secondary" title="Forzar Autoguardado">
                        <i class="fas fa-save"></i> Autoguardar Ahora
                    </button>
                    <span id="autoSaveStatus" class="ml-2 text-sm text-muted"></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                            <label for="client_id">Cliente <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-control select2 @error('client_id') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clients as $id => $name)
                                    <option value="{{ $id }}" {{ (old('client_id', $quote->client_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="issue_date">Fecha Emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $quote->issue_date->format('Y-m-d')) }}" required>
                            @error('issue_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="expiry_date">Fecha Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', $quote->expiry_date->format('Y-m-d')) }}" required>
                            @error('expiry_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                     <div class="col-md-2">
                        <div class="form-group">
                            <label for="base_currency_display">Moneda</label>
                            <input type="text" id="base_currency_display" class="form-control" value="{{ $quote->base_currency }}" readonly title="La moneda base no se puede cambiar al editar.">
                            <input type="hidden" name="base_currency" value="{{ $quote->base_currency }}">
                        </div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_bcv">Tasa BCV (Opcional)</label>
                            <input type="number" step="any" name="exchange_rate_bcv" id="exchange_rate_bcv" class="form-control @error('exchange_rate_bcv') is-invalid @enderror rate-input" value="{{ old('exchange_rate_bcv', number_format($current_bcv_rate ?? 0, 2, '.', '')) }}" placeholder="Tasa BCV del día">
                             @error('exchange_rate_bcv') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_promedio">Tasa Promedio (Opcional)</label>
                            <input type="number" step="any" name="exchange_rate_promedio" id="exchange_rate_promedio" class="form-control @error('exchange_rate_promedio') is-invalid @enderror rate-input" value="{{ old('exchange_rate_promedio', number_format($current_promedio_rate ?? 0, 2, '.', '')) }}" placeholder="Tasa promedio del día">
                            @error('exchange_rate_promedio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="profit_percentage">Porcentaje Utilidad (%)</label>
                            <input type="number" step="0.01" name="profit_percentage" id="profit_percentage" class="form-control @error('profit_percentage') is-invalid @enderror calc-trigger" value="{{ old('profit_percentage', number_format($current_profit_percentage ?? 0, 2, '.', '')) }}" placeholder="Ej: 20.00">
                            @error('profit_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Ítems de la Cotización (Editando) <span class="text-danger">*</span></h3>
                 <div class="card-tools">
                    <button type="button" id="addManualItemButton" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Añadir Ítem Manual</button>
                     <button type="button" id="searchProductButton" class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar Producto</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm" id="quoteItemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Producto/Servicio <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Cantidad <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Medida</th>
                                <th style="width: 12%;">Costo Unit. <span class="text-danger">*</span></th>
                                <th style="width: 5%;">+IVA?</th>
                                <th style="width: 16%;">Cálculo Precio</th>
                                <th style="width: 12%;">Precio Unit. <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Total</th>
                                <th style="width: 3%;">Acc.</th>
                            </tr>
                        </thead>
                        <tbody id="quoteItemsTbody">
                            @php $item_idx_server = 0; @endphp
                            @foreach(old('items', $quote->items->map(function($item_model) {
                                $item_array = $item_model->toArray();
                                if ($item_model->product) {
                                    $item_array['product'] = $item_model->product->toArray();
                                }
                                $item_array['cost_applies_iva'] = $item_model->cost_applies_iva; // Pasar el valor de la BD
                                return $item_array;
                            })) as $index => $item_data)
                            @php
                                $item = (object) $item_data;
                                $productId = $item->product_id ?? null;
                                $productData = $item->product ?? null;
                                
                                $manualProductName = $item->manual_product_name ?? ($productData['name'] ?? ($productData->name ?? ''));
                                $manualProductUnit = $item->manual_product_unit ?? ($productData['unit_of_measure'] ?? ($productData->unit_of_measure ?? 'Medida'));
                                $cost = $item->cost ?? 0;
                                $quantity = $item->quantity ?? 1;
                                $price = $item->price ?? $cost;
                                $priceCalculationMethod = $item->price_calculation_method ?? 'manual';
                                $appliedRateValue = $item->applied_rate_value ?? null;
                                $itemId = $item->id ?? null;
                                $costPlusIva = $item->cost_applies_iva ?? (old('items.'.$index.'.cost_plus_iva') == '1');
                            @endphp
                            <tr class="quote-item-row">
                                <td>
                                    <input type="hidden" name="items[{{ $item_idx_server }}][id]" value="{{ $itemId }}">
                                    <input type="hidden" name="items[{{ $item_idx_server }}][product_id]" value="{{ $productId }}" class="item-product-id">
                                    <input type="text" name="items[{{ $item_idx_server }}][manual_product_name]" class="form-control form-control-sm item-name @error('items.'.$item_idx_server.'.manual_product_name') is-invalid @enderror" value="{{ $manualProductName }}" placeholder="Nombre del Producto" required>
                                </td>
                                <td><input type="number" name="items[{{ $item_idx_server }}][quantity]" class="form-control form-control-sm item-quantity calc-trigger @error('items.'.$item_idx_server.'.quantity') is-invalid @enderror" value="{{ $quantity }}" step="any" min="0.01" required></td>
                                <td><input type="text" name="items[{{ $item_idx_server }}][manual_product_unit]" class="form-control form-control-sm item-unit" value="{{ $manualProductUnit }}" placeholder="Ej: Pza, Kg"></td>
                                <td>
                                    <input type="number" name="items[{{ $item_idx_server }}][cost]" class="form-control form-control-sm item-cost calc-trigger @error('items.'.$item_idx_server.'.cost') is-invalid @enderror" value="{{ number_format((float)$cost, 2, '.', '') }}" step="any" min="0" required>
                                </td>
                                <td>
                                    <div class="form-check text-center">
                                        <input type="checkbox" name="items[{{ $item_idx_server }}][cost_plus_iva]" value="1" class="form-check-input item-cost-plus-iva calc-trigger" style="margin-top: 0.3rem; margin-left: -0.5rem;" {{ $costPlusIva ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <input type="hidden" name="items[{{ $item_idx_server }}][price_calculation_method]" class="item-price-calc-method" value="{{ $priceCalculationMethod }}">
                                    <input type="hidden" name="items[{{ $item_idx_server }}][applied_rate_value]" class="item-applied-rate" value="{{ $appliedRateValue ? number_format((float)$appliedRateValue, 2, '.', '') : '' }}">
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-xs btn-outline-info calc-price-btn {{ $priceCalculationMethod == 'promedio' ? 'active' : '' }}" data-method="promedio" title="Usar Tasa Promedio">
                                            <input type="radio" name="items[{{ $item_idx_server }}][calc_option]" value="promedio" class="calc-trigger" {{ $priceCalculationMethod == 'promedio' ? 'checked' : '' }}> <span class="d-none d-sm-inline">Prom.</span>
                                        </label>
                                        <label class="btn btn-xs btn-outline-warning calc-price-btn {{ $priceCalculationMethod == 'bcv' ? 'active' : '' }}" data-method="bcv" title="Usar Tasa BCV">
                                            <input type="radio" name="items[{{ $item_idx_server }}][calc_option]" value="bcv" class="calc-trigger" {{ $priceCalculationMethod == 'bcv' ? 'checked' : '' }}> <span class="d-none d-sm-inline">BCV</span>
                                        </label>
                                        <label class="btn btn-xs btn-outline-secondary calc-price-btn {{ $priceCalculationMethod == 'manual' ? 'active' : '' }}" data-method="manual" title="Precio Manual">
                                            <input type="radio" name="items[{{ $item_idx_server }}][calc_option]" value="manual" class="calc-trigger" {{ $priceCalculationMethod == 'manual' ? 'checked' : '' }}> <i class="fas fa-edit"></i>
                                        </label>
                                    </div>
                                </td>
                                <td><input type="number" name="items[{{ $item_idx_server }}][price]" class="form-control form-control-sm item-price calc-trigger @error('items.'.$item_idx_server.'.price') is-invalid @enderror" value="{{ number_format((float)$price, 2, '.', '') }}" step="any" min="0" required {{ $priceCalculationMethod != 'manual' ? 'readonly' : '' }}></td>
                                <td class="text-right"><span class="item-line-total">0.00</span></td>
                                <td><button type="button" class="btn btn-xs btn-danger removeItemButton" data-item-id="{{ $itemId }}"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @php $item_idx_server++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                    <div id="deletedItemsContainer"></div>
                </div>
                @error('items') <span class="text-danger ml-2">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                 <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title">Notas y Términos</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="terms_and_conditions">Términos y Condiciones</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control" rows="3">{{ old('terms_and_conditions', $current_terms_conditions ?? '') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente (Opcional)</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control" rows="2">{{ old('notes_to_client', $quote->notes_to_client) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="internal_notes">Notas Internas (No visible al cliente, Opcional)</label>
                            <textarea name="internal_notes" id="internal_notes" class="form-control" rows="2">{{ old('internal_notes', $quote->internal_notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Resumen y Descuentos</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th>Subtotal Bruto:</th><td class="text-right" id="quoteSubtotalText">{{ number_format($quote->subtotal, 2, ',', '.') }}</td></tr>
                            <tr>
                                <td>
                                    <label for="discount_type">Descuento Global:</label>
                                    <select name="discount_type" id="discount_type" class="form-control form-control-sm d-inline-block calc-trigger" style="width: auto;">
                                        <option value="" {{ old('discount_type', $quote->discount_type) == '' ? 'selected' : '' }}>Ninguno</option>
                                        <option value="fixed" {{ old('discount_type', $quote->discount_type) == 'fixed' ? 'selected' : '' }}>Monto Fijo</option>
                                        <option value="percentage" {{ old('discount_type', $quote->discount_type) == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <input type="number" step="any" name="discount_value" id="discount_value" class="form-control form-control-sm d-inline-block calc-trigger @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $quote->discount_value) }}" style="width: 100px;">
                                    @error('discount_value') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                             <tr><th>Monto Descuento:</th><td class="text-right" id="discountAmountText">{{ number_format($quote->discount_amount, 2, ',', '.') }}</td></tr>
                            <tr><th>Base Imponible:</th><td class="text-right" id="taxableBaseText">{{ number_format($quote->subtotal - $quote->discount_amount, 2, ',', '.') }}</td></tr>
                            <tr>
                                <td>
                                    <label for="tax_percentage">Impuesto (%):</label>
                                    <input type="number" step="any" name="tax_percentage" id="taxPercentageInput" class="form-control form-control-sm d-inline-block calc-trigger @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $quote->tax_percentage) }}" required style="width: 80px;">
                                     @error('tax_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </td>
                                <td class="text-right" id="taxAmountText">{{ number_format($quote->tax_amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-light"><th class="h4">Total Cotización:</th><td class="text-right h4" id="quoteTotalText">{{ number_format($quote->total, 2, ',', '.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Cotización</button>
            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

    <div class="modal fade" id="searchProductModal" tabindex="-1" role="dialog" aria-labelledby="searchProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchProductModalLabel">Buscar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" id="productSearchInput" class="form-control" placeholder="Buscar por nombre o código...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="performProductSearch"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="list-group" id="productSearchResults"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@push('js')
<script>
$(document).ready(function() {
    console.log("Edit Quote JS: Documento listo para Edición 6.");

    if ($.fn.select2) {
        $('#client_id').select2({ placeholder: "Seleccione un cliente", allowClear: true });
    }

    let globalItemIndex = {{ $item_idx_server }}; 
    console.log("Edit Quote JS: globalItemIndex inicial es " + globalItemIndex);

    function addQuoteItemRow(item = {}, currentIndex, isFromAutoSave = false) {
        console.log(`Edit Quote JS: addQuoteItemRow para índice ${currentIndex} con:`, item);
        let costValue = item.cost ? parseFloat(item.cost).toFixed(2) : '0.00';
        let priceValue = item.price ? parseFloat(item.price).toFixed(2) : costValue;
        let quantityValue = item.quantity || 1;
        let productName = item.manual_product_name || item.name || '';
        let productUnit = item.manual_product_unit || item.unit_of_measure || 'Medida';
        let productId = item.product_id || '';
        let itemId = item.id || '';
        
        let costPlusIvaChecked = '';
        if (isFromAutoSave && (item.cost_plus_iva === '1' || item.cost_plus_iva === true)) {
            costPlusIvaChecked = 'checked';
        } else if (!isFromAutoSave && item.cost_applies_iva) { 
            costPlusIvaChecked = 'checked';
        }
        
        let calcMethodForRender = item.price_calculation_method || 'manual';
        if(isFromAutoSave && item.calc_option) calcMethodForRender = item.calc_option;

        let activePromedio = (calcMethodForRender === 'promedio') ? 'active' : '';
        let checkedPromedio = (calcMethodForRender === 'promedio') ? 'checked' : '';
        let activeBcv = (calcMethodForRender === 'bcv') ? 'active' : '';
        let checkedBcv = (calcMethodForRender === 'bcv') ? 'checked' : '';
        let activeManual = (calcMethodForRender === 'manual') ? 'active' : '';
        let checkedManual = (calcMethodForRender === 'manual') ? 'checked' : '';
        let priceReadOnly = (calcMethodForRender !== 'manual') ? 'readonly' : '';

        const newRowHtml = `
            <tr class="quote-item-row">
                <td>
                    <input type="hidden" name="items[${currentIndex}][id]" value="${itemId}">
                    <input type="hidden" name="items[${currentIndex}][product_id]" value="${productId}" class="item-product-id">
                    <input type="text" name="items[${currentIndex}][manual_product_name]" class="form-control form-control-sm item-name" value="${productName}" placeholder="Nombre del Producto" required>
                </td>
                <td><input type="number" name="items[${currentIndex}][quantity]" class="form-control form-control-sm item-quantity calc-trigger" value="${quantityValue}" step="any" min="0.01" required></td>
                <td><input type="text" name="items[${currentIndex}][manual_product_unit]" class="form-control form-control-sm item-unit" value="${productUnit}" placeholder="Ej: Pza, Kg"></td>
                <td>
                    <input type="number" name="items[${currentIndex}][cost]" class="form-control form-control-sm item-cost calc-trigger" value="${costValue}" step="any" min="0" required>
                </td>
                <td>
                    <div class="form-check text-center">
                        <input type="checkbox" name="items[${currentIndex}][cost_plus_iva]" value="1" class="form-check-input item-cost-plus-iva calc-trigger" style="margin-top: 0.3rem; margin-left: -0.5rem;" ${costPlusIvaChecked}>
                    </div>
                </td>
                <td>
                    <input type="hidden" name="items[${currentIndex}][price_calculation_method]" class="item-price-calc-method" value="${calcMethodForRender}">
                    <input type="hidden" name="items[${currentIndex}][applied_rate_value]" class="item-applied-rate" value="${item.applied_rate_value || ''}">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-xs btn-outline-info calc-price-btn ${activePromedio}" data-method="promedio" title="Usar Tasa Promedio">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="promedio" class="calc-trigger" ${checkedPromedio}> <span class="d-none d-sm-inline">Prom.</span>
                        </label>
                        <label class="btn btn-xs btn-outline-warning calc-price-btn ${activeBcv}" data-method="bcv" title="Usar Tasa BCV">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="bcv" class="calc-trigger" ${checkedBcv}> <span class="d-none d-sm-inline">BCV</span>
                        </label>
                        <label class="btn btn-xs btn-outline-secondary calc-price-btn ${activeManual}" data-method="manual" title="Precio Manual">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="manual" class="calc-trigger" ${checkedManual}> <i class="fas fa-edit"></i>
                        </label>
                    </div>
                </td>
                <td><input type="number" name="items[${currentIndex}][price]" class="form-control form-control-sm item-price calc-trigger" value="${priceValue}" step="any" min="0" required ${priceReadOnly}></td>
                <td class="text-right"><span class="item-line-total">0.00</span></td>
                <td><button type="button" class="btn btn-xs btn-danger removeItemButton" data-item-id="${itemId}"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#quoteItemsTbody').append(newRowHtml);
        $('[data-toggle="tooltip"]').tooltip();
        if (!isFromAutoSave) {
            // No se incrementa globalItemIndex aquí para addQuoteItemRow en edit,
            // ya que el bucle PHP de items existentes usa $item_idx_server.
            // globalItemIndex se usa para *nuevos* items añadidos manualmente.
        }
        calculateAll();
    }
    
    $('#addManualItemButton').off('click').on('click', function() { addQuoteItemRow({}, globalItemIndex); globalItemIndex++; });
    $('#searchProductButton').off('click').on('click', function() { if ($.fn.modal) $('#searchProductModal').modal('show');});
    $('#performProductSearch').off('click').on('click', function() {
        let searchTerm = $('#productSearchInput').val(); if (searchTerm.length < 2) { $('#productSearchResults').html('<a href="#" class="list-group-item list-group-item-action disabled">Escriba al menos 2 caracteres.</a>'); return; }
        $('#productSearchResults').html('<a href="#" class="list-group-item list-group-item-action disabled">Buscando...</a>');
        $.ajax({ url: "{{ route('quotes.searchProducts') }}", data: { term: searchTerm, _token: "{{ csrf_token() }}" }, dataType: 'json',
            success: function(products) {
                let html = ''; if (products && products.length > 0) { products.forEach(p => { let productJson = JSON.stringify(p).replace(/'/g, "&apos;"); html += `<a href="#" class="list-group-item list-group-item-action select-product" data-product='${productJson}'>${p.name || 'Sin Nombre'} (${p.code || 'S/C'}) - Costo: ${parseFloat(p.cost || 0).toFixed(2)}</a>`; });
                } else { html = '<a href="#" class="list-group-item list-group-item-action disabled">No se encontraron productos.</a>'; } $('#productSearchResults').html(html);
            },
            error: function(jqXHR) { console.error("Error en AJAX searchProducts:", jqXHR.responseText); $('#productSearchResults').html('<a href="#" class="list-group-item list-group-item-action disabled text-danger">Error al buscar.</a>');}
        });
    });
    $(document).on('click', '.select-product', function(e) {
        e.preventDefault(); let productData = $(this).data('product'); if (typeof productData === 'string') productData = JSON.parse(productData.replace(/&apos;/g, "'"));
        addQuoteItemRow({ product_id: productData.id, name: productData.name, unit_of_measure: productData.unit_of_measure, cost: productData.cost, price: productData.cost }, globalItemIndex); globalItemIndex++;
        if ($.fn.modal) $('#searchProductModal').modal('hide'); $('#productSearchInput').val(''); $('#productSearchResults').html('');
    });
    $(document).on('click', '.removeItemButton', function() {
        let itemId = $(this).data('item-id'); if (itemId) { $('#deletedItemsContainer').append(`<input type="hidden" name="deleted_items[]" value="${itemId}">`); }
        $(this).closest('tr').remove(); calculateAll();
    });

    function formatCurrency(num) { if (isNaN(parseFloat(num))) return '0,00'; return parseFloat(num).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');}
    
    $(document).on('input change', '.calc-trigger, .rate-input, #profit_percentage, .item-cost-plus-iva, #discount_type, #discount_value, #taxPercentageInput', calculateAll);
    $(document).on('click', '.calc-price-btn', function() {
        $(this).addClass('active').siblings().removeClass('active');
        $(this).find('input[type="radio"]').prop('checked', true);
        let row = $(this).closest('tr');
        let priceInput = row.find('.item-price');
        if ($(this).data('method') === 'manual') {
            priceInput.prop('readonly', false);
        } else {
            priceInput.prop('readonly', true);
        }
        calculateAll();
    });

    // FUNCIÓN calculateAll() CON LÓGICA COMPLETA
    function calculateAll() {
        console.log("calculateAll() INICIADO con nueva lógica.");
        let subtotalGeneral = 0;
        
        const globalBcvRate = parseFloat($('#exchange_rate_bcv').val().replace(',', '.')) || 0;
        const globalPromedioRate = parseFloat($('#exchange_rate_promedio').val().replace(',', '.')) || 0;
        const globalProfitPercentage = parseFloat($('#profit_percentage').val().replace(',', '.')) || 0;
        const systemIVARateForCost = 16.0; 

        $('#quoteItemsTbody tr.quote-item-row').each(function(rowIndex) {
            let row = $(this);
            let quantity = parseFloat(row.find('.item-quantity').val().replace(',', '.')) || 0;
            let itemCostBase = parseFloat(row.find('.item-cost').val().replace(',', '.')) || 0;
            let priceInput = row.find('.item-price');
            
            let costoAjustadoPorIVA = itemCostBase;
            const costPlusIvaCheckbox = row.find('.item-cost-plus-iva');
            if (costPlusIvaCheckbox.is(':checked')) {
                costoAjustadoPorIVA = itemCostBase * (1 + (systemIVARateForCost / 100));
            }

            let precioIntermedio;
            let calcMethod = row.find('input[name*="[calc_option]"]:checked').val() || 'manual';
            row.find('.item-price-calc-method').val(calcMethod);
            row.find('.item-applied-rate').val('');

            if (calcMethod === 'manual') {
                priceInput.prop('readonly', false);
                precioIntermedio = parseFloat(priceInput.val().replace(',', '.')) || 0; 
            } else {
                priceInput.prop('readonly', true);
                if (calcMethod === 'promedio') {
                    if (globalPromedioRate > 0 && globalBcvRate > 0) {
                        precioIntermedio = (costoAjustadoPorIVA * globalPromedioRate) / globalBcvRate;
                        row.find('.item-applied-rate').val((globalPromedioRate / globalBcvRate).toFixed(4));
                    } else {
                        precioIntermedio = costoAjustadoPorIVA; 
                        row.find('.calc-price-btn[data-method="manual"]').addClass('active').siblings().removeClass('active').find('input').prop('checked',true);
                        priceInput.prop('readonly', false).val(precioIntermedio.toFixed(2));
                        row.find('.item-price-calc-method').val('manual');
                         console.warn(`Ítem ${rowIndex + 1}: Cálculo Promedio no posible, tasas no válidas. Cambiado a manual.`);
                    }
                } else if (calcMethod === 'bcv') {
                    precioIntermedio = costoAjustadoPorIVA; 
                    row.find('.item-applied-rate').val(globalBcvRate > 0 ? globalBcvRate.toFixed(4) : ''); 
                } else { 
                     precioIntermedio = costoAjustadoPorIVA;
                     priceInput.prop('readonly', false).val(precioIntermedio.toFixed(2));
                     row.find('.calc-price-btn[data-method="manual"]').addClass('active').siblings().removeClass('active').find('input').prop('checked',true);
                     row.find('.item-price-calc-method').val('manual');
                }
            }

            let precioUnitarioFinal = precioIntermedio;
            if (globalProfitPercentage > 0) {
                precioUnitarioFinal = precioIntermedio * (1 + (globalProfitPercentage / 100));
            }
            priceInput.val(precioUnitarioFinal.toFixed(2));

            let lineTotal = quantity * precioUnitarioFinal;
            row.find('.item-line-total').text(formatCurrency(lineTotal));
            subtotalGeneral += lineTotal;
        });

        $('#quoteSubtotalText').text(formatCurrency(subtotalGeneral));
        let discountValue = parseFloat($('#discount_value').val().replace(',', '.')) || 0;
        let discountType = $('#discount_type').val();
        let discountAmount = 0;
        if (discountType === 'percentage' && discountValue > 0) { discountAmount = (subtotalGeneral * discountValue) / 100; } 
        else if (discountType === 'fixed') { discountAmount = discountValue; }
        discountAmount = Math.max(0, Math.min(discountAmount, subtotalGeneral));
        $('#discountAmountText').text(formatCurrency(discountAmount));
        let taxableBase = subtotalGeneral - discountAmount;
        $('#taxableBaseText').text(formatCurrency(taxableBase));
        let globalQuoteIVARate = parseFloat($('#taxPercentageInput').val().replace(',', '.')) || 0;
        let taxAmount = (taxableBase * globalQuoteIVARate) / 100;
        $('#taxAmountText').text(formatCurrency(taxAmount));
        let totalFinal = taxableBase + taxAmount;
        $('#quoteTotalText').text(formatCurrency(totalFinal));
    }
    
    // Inicialización y Restauración desde Autosave
    $('#quoteItemsTbody tr.quote-item-row').each(function(){ 
        let row = $(this); 
        let method = row.find('.item-price-calc-method').val(); 
        row.find('.calc-price-btn').removeClass('active'); 
        row.find('input[name*="[calc_option]"]').prop('checked', false);
        if(method === 'bcv'){ row.find('.calc-price-btn[data-method="bcv"]').addClass('active').find('input').prop('checked', true); }
        else if (method === 'promedio'){ row.find('.calc-price-btn[data-method="promedio"]').addClass('active').find('input').prop('checked', true); }
        else { row.find('.calc-price-btn[data-method="manual"]').addClass('active').find('input').prop('checked', true); }
    });
    calculateAll(); 

    let autoSaveTimeout; const autoSaveDelay = 10000; const quoteIdForJsAutosave = $('#quote_id_for_autosave').val();
    function triggerAutoSave(showAlert = false) {
        clearTimeout(autoSaveTimeout); if (!quoteIdForJsAutosave) return;
        $('#autoSaveStatus').html('<i class="fas fa-spinner fa-spin"></i> Autoguardando...');
        let formDataObject = {}; $('#editQuoteForm').serializeArray().forEach(field => { if (field.name !== '_token' && field.name !== '_method') formDataObject[field.name] = field.value; });
        let itemsArray = [];
        $('#quoteItemsTbody tr.quote-item-row').each(function() {
            let row = $(this);
            let currentItemIndexNameMatch = row.find('input[name^="items["]').attr('name').match(/items\[(\d+)\]/);
            if (!currentItemIndexNameMatch) return; 
            let currentItemIndexName = currentItemIndexNameMatch[1];
            let item = { 
                id: row.find(`input[name="items[${currentItemIndexName}][id]"]`).val() || null, 
                product_id: row.find(`input[name="items[${currentItemIndexName}][product_id]"]`).val() || null, 
                manual_product_name: row.find(`input[name="items[${currentItemIndexName}][manual_product_name]"]`).val(), 
                quantity: row.find(`input[name="items[${currentItemIndexName}][quantity]"]`).val(), 
                manual_product_unit: row.find(`input[name="items[${currentItemIndexName}][manual_product_unit]"]`).val(), 
                cost: row.find(`input[name="items[${currentItemIndexName}][cost]"]`).val(), 
                price: row.find(`input[name="items[${currentItemIndexName}][price]"]`).val(), 
                price_calculation_method: row.find(`input[name="items[${currentItemIndexName}][price_calculation_method]"]`).val(), 
                applied_rate_value: row.find(`input[name="items[${currentItemIndexName}][applied_rate_value]"]`).val(), 
                cost_plus_iva: row.find(`input[name="items[${currentItemIndexName}][cost_plus_iva]"]`).is(':checked') ? '1' : '0',
                calc_option: row.find(`input[name="items[${currentItemIndexName}][calc_option]"]:checked`).val() 
            };
            itemsArray.push(item);
        });
        formDataObject.items = itemsArray; for (let key in formDataObject) if (key.startsWith('items[')) delete formDataObject[key];
        
        $.ajax({ url: `/quotes/autosave/${quoteIdForJsAutosave}`, method: 'POST', data: { _token: "{{ csrf_token() }}", ...formDataObject },
            beforeSend: () => $('#autoSaveTriggerButton').prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Guardando...'),
            success: function(response) { $('#autoSaveStatus').html('<i class="fas fa-check-circle text-success"></i> Autoguardado: ' + new Date().toLocaleTimeString()); if (showAlert) alert(response.message || 'Progreso autoguardado.');},
            error: function(xhr) { let errorMsg = xhr.responseJSON?.message || 'Error al autoguardar.'; $('#autoSaveStatus').html('<i class="fas fa-exclamation-circle text-danger"></i> Error autoguardado'); if (showAlert) alert(errorMsg); console.error('Autosave error:', xhr);},
            complete: () => $('#autoSaveTriggerButton').prop('disabled', false).html('<i class="fas fa-save"></i> Autoguardar Ahora')
        });
    }
    $('#editQuoteForm input, #editQuoteForm textarea, #editQuoteForm select').on('input change', function() { clearTimeout(autoSaveTimeout); $('#autoSaveStatus').html('<i class="far fa-clock"></i> Cambios pendientes...'); autoSaveTimeout = setTimeout(function() { triggerAutoSave(false); }, autoSaveDelay); });
    $('#autoSaveTriggerButton').on('click', function() { triggerAutoSave(true); });
    
    @if($quote->auto_save_data && is_array($quote->auto_save_data) && !empty($quote->auto_save_data) && !request()->has('restored'))
        if (confirm('Hay datos autoguardados. ¿Desea restaurarlos?')) {
            let autoSavedData = @json($quote->auto_save_data);
            $('#client_id').val(autoSavedData.client_id).trigger('change'); $('#issue_date').val(autoSavedData.issue_date); $('#expiry_date').val(autoSavedData.expiry_date);
            $('#exchange_rate_bcv').val(autoSavedData.exchange_rate_bcv); $('#exchange_rate_promedio').val(autoSavedData.exchange_rate_promedio);
            $('#profit_percentage').val(autoSavedData.profit_percentage);
            $('#terms_and_conditions').val(autoSavedData.terms_and_conditions); $('#notes_to_client').val(autoSavedData.notes_to_client); $('#internal_notes').val(autoSavedData.internal_notes);
            $('#discount_type').val(autoSavedData.discount_type); $('#discount_value').val(autoSavedData.discount_value); $('#taxPercentageInput').val(autoSavedData.tax_percentage);
            $('#quoteItemsTbody').empty(); globalItemIndex = 0; $('#deletedItemsContainer').empty();
            if (autoSavedData.items && Array.isArray(autoSavedData.items)) { 
                autoSavedData.items.forEach(item => { 
                    item.cost = parseFloat(item.cost || 0); 
                    item.price = parseFloat(item.price || 0); 
                    item.quantity = parseFloat(item.quantity || 1);
                    // Para restaurar el checkbox +IVA desde autosave
                    item.cost_applies_iva = (item.cost_plus_iva === '1' || item.cost_plus_iva === true); 
                    addQuoteItemRow(item, globalItemIndex, true); 
                    globalItemIndex++;
                }); 
            }
            calculateAll(); $('#autoSaveStatus').html('<i class="fas fa-undo"></i> Datos restaurados.');
            $.ajax({ url: `/quotes/autosave/${quoteIdForJsAutosave}`, method: 'POST', data: { _token: "{{ csrf_token() }}", clear_autosave: true }, success: function() { console.log('AutoSave data cleared from DB.'); }});
        }
    @endif
});
</script>
@endpush
