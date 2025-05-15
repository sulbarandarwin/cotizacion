@extends('adminlte::page')

@section('title', 'Editar Cotización #' . $quote->quote_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Editar Cotización #{{ $quote->quote_number }}</h1>
        <a href="{{ route('quotes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a la Lista</a>
    </div>
@stop

@section('content')
    {{-- Mostrar errores de validación generales o de sesión --}}
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


    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">Información General y Cliente</h3>
        </div>
        <form action="{{ route('quotes.update', $quote->id) }}" method="POST" id="quoteForm">
            @method('PUT')
            @csrf
            <div class="card-body">
                {{-- Fila para Cliente, Estado y Fechas --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="client_id">Cliente <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente...</option>
                                @if(isset($clients) && $clients->count() > 0)
                                    @foreach($clients as $id => $name)
                                        <option value="{{ $id }}" {{ old('client_id', $quote->client_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('client_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado Actual</label>
                            <input type="text" class="form-control" value="{{ $quote->status_text }}" readonly> {{-- Usar accessor status_text --}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="issue_date">Fecha de Emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $quote->issue_date->format('Y-m-d')) }}" required>
                            @error('issue_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expiry_date">Fecha de Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', $quote->expiry_date->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Moneda y Tasas (Solo mostrar si es relevante o si se permite cambiar) --}}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="base_currency_display">Moneda Base</label> {{-- Cambiado a _display para no enviar --}}
                            <input type="text" id="base_currency_display" class="form-control" value="{{ $quote->base_currency }}" readonly>
                            {{-- La moneda base no se envía para actualización usualmente --}}
                            {{-- <input type="hidden" name="base_currency" value="{{ $quote->base_currency }}"> --}}
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_bcv">Tasa BCV (Opcional)</label>
                            <input type="number" name="exchange_rate_bcv" id="exchange_rate_bcv" class="form-control @error('exchange_rate_bcv') is-invalid @enderror" value="{{ old('exchange_rate_bcv', $quote->exchange_rate_bcv) }}" step="any" min="0">
                            @error('exchange_rate_bcv') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_promedio">Tasa Promedio (Opcional)</label>
                            <input type="number" name="exchange_rate_promedio" id="exchange_rate_promedio" class="form-control @error('exchange_rate_promedio') is-invalid @enderror" value="{{ old('exchange_rate_promedio', $quote->exchange_rate_promedio) }}" step="any" min="0">
                            @error('exchange_rate_promedio') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                </div>


                {{-- SECCIÓN DE ÍTEMS DE LA COTIZACIÓN --}}
                <hr>
                <div class="row mt-3 mb-1">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h4>Ítems de la Cotización <span class="text-danger">*</span></h4>
                        <button type="button" id="addManualItemButton" class="btn btn-info btn-sm"><i class="fas fa-plus"></i> Añadir Ítem</button>
                    </div>
                </div>
                @error('items') <div class="row"><div class="col-12"><span class="text-danger"><strong>{{ $message }}</strong></span></div></div> @enderror


                <div class="table-responsive mt-2">
                    <table class="table table-bordered table-hover" id="quoteItemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Producto/Servicio <span class="text-danger">*</span></th>
                                <th style="width: 10%;" class="text-center">Cantidad <span class="text-danger">*</span></th>
                                <th style="width: 13%;" class="text-center">Costo Unit. ({{$quote->base_currency}}) <span class="text-danger">*</span></th>
                                <th style="width: 22%;" class="text-center">Cálculo Precio</th>
                                <th style="width: 13%;" class="text-center">Precio Unit. ({{$quote->base_currency}}) <span class="text-danger">*</span></th>
                                <th style="width: 12%;" class="text-center">Total Línea ({{$quote->base_currency}})</th>
                                <th style="width: 5%;" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="quoteItemsTbody">
                            @php
                                $itemsToDisplay = old('items', $quote->items->map(function ($item, $key) {
                                    return [
                                        'id' => $item->id,
                                        'manual_product_name' => $item->manual_product_name ?? '',
                                        'quantity' => $item->quantity,
                                        'cost' => $item->cost,
                                        'price_calculation_method' => $item->price_calculation_method,
                                        'applied_rate_value' => $item->applied_rate_value,
                                        'price' => $item->price,
                                    ];
                                })->toArray());
                            @endphp

                            @if(is_array($itemsToDisplay) && count($itemsToDisplay) > 0)
                                @foreach($itemsToDisplay as $key => $itemData)
                                    <tr>
                                        <td>
                                            @if(isset($itemData['id']))
                                                <input type="hidden" name="items[{{ $key }}][id]" value="{{ $itemData['id'] }}">
                                            @endif
                                            <input type="text" name="items[{{ $key }}][manual_product_name]" class="form-control item-name @error('items.'.$key.'.manual_product_name') is-invalid @enderror" value="{{ $itemData['manual_product_name'] ?? '' }}" placeholder="Nombre del producto/servicio" required>
                                            @error('items.'.$key.'.manual_product_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][quantity]" class="form-control item-quantity text-right @error('items.'.$key.'.quantity') is-invalid @enderror" value="{{ $itemData['quantity'] ?? 1 }}" min="0.01" step="any" required></td>
                                        <td><input type="number" name="items[{{ $key }}][cost]" class="form-control item-cost text-right @error('items.'.$key.'.cost') is-invalid @enderror" value="{{ number_format((float)($itemData['cost'] ?? 0.00), 2, '.', '') }}" min="0" step="any" required></td>
                                        <td class="text-center">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_promedio_display]" id="calc_promedio_{{ $key }}" value="promedio" {{ (isset($itemData['price_calculation_method']) && $itemData['price_calculation_method'] == 'promedio') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_promedio_{{ $key }}">Promedio</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_bcv_display]" id="calc_bcv_{{ $key }}" value="bcv" {{ (isset($itemData['price_calculation_method']) && $itemData['price_calculation_method'] == 'bcv') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_bcv_{{ $key }}">BCV</label>
                                            </div>
                                            <input type="hidden" name="items[{{ $key }}][price_calculation_method]" class="item-calc-method-hidden" value="{{ $itemData['price_calculation_method'] ?? '' }}">
                                            <input type="hidden" name="items[{{ $key }}][applied_rate_value]" class="item-applied-rate-hidden" value="{{ $itemData['applied_rate_value'] ?? '' }}">
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][price]" class="form-control item-price text-right @error('items.'.$key.'.price') is-invalid @enderror" value="{{ number_format((float)($itemData['price'] ?? 0.00), 2, '.', '') }}" min="0" step="any" {{ (isset($itemData['price_calculation_method']) && !empty($itemData['price_calculation_method'])) ? 'readonly' : '' }} required></td>
                                        <td class="item-line-total text-right font-weight-bold">0.00</td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeItemButton"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <template id="quoteItemTemplate">
                    <tr>
                        <td>
                            <input type="text" name="items[__INDEX__][manual_product_name]" class="form-control item-name" placeholder="Nombre del producto/servicio" required>
                        </td>
                        <td><input type="number" name="items[__INDEX__][quantity]" class="form-control item-quantity text-right" value="1" min="0.01" step="any" required></td>
                        <td><input type="number" name="items[__INDEX__][cost]" class="form-control item-cost text-right" value="0.00" min="0" step="any" required></td>
                        <td class="text-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_promedio_display]" id="calc_promedio___INDEX__" value="promedio">
                                <label class="form-check-label" for="calc_promedio___INDEX__">Promedio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_bcv_display]" id="calc_bcv___INDEX__" value="bcv">
                                <label class="form-check-label" for="calc_bcv___INDEX__">BCV</label>
                            </div>
                             <input type="hidden" name="items[__INDEX__][price_calculation_method]" class="item-calc-method-hidden" value="">
                             <input type="hidden" name="items[__INDEX__][applied_rate_value]" class="item-applied-rate-hidden" value="">
                        </td>
                        <td><input type="number" name="items[__INDEX__][price]" class="form-control item-price text-right" value="0.00" min="0" step="any" required></td>
                        <td class="item-line-total text-right font-weight-bold">0.00</td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeItemButton"><i class="fas fa-trash"></i></button></td>
                    </tr>
                </template>
                <div id="deletedItemsContainer">
                    {{-- Inputs ocultos para ítems eliminados se añadirán aquí por JS --}}
                </div>


                {{-- SECCIÓN DE TOTALES --}}
                <hr class="mt-4">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th style="width:50%">Subtotal:</th>
                                    <td id="quoteSubtotalText" class="text-right font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <th>Descuento Global:</th>
                                    <td class="text-right">
                                        <div class="input-group input-group-sm" style="width: 200px; float: right;">
                                            <input type="number" name="discount_value" id="discount_value" class="form-control text-right @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $quote->discount_value ?? 0) }}" min="0" step="any">
                                            <div class="input-group-append">
                                                <select name="discount_type" id="discount_type" class="form-control @error('discount_type') is-invalid @enderror">
                                                    <option value="fixed" {{ old('discount_type', $quote->discount_type) == 'fixed' ? 'selected' : '' }}>Monto ({{$quote->base_currency}})</option>
                                                    <option value="percentage" {{ old('discount_type', $quote->discount_type) == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                                </select>
                                            </div>
                                        </div>
                                        @error('discount_value') <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <th>Monto Descuento (-):</th>
                                    <td id="discountAmountText" class="text-right text-danger font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <th>Base Imponible:</th>
                                    <td id="taxableBaseText" class="text-right font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <th>IVA (<input type="number" name="tax_percentage" id="taxPercentageInput" value="{{ old('tax_percentage', $quote->tax_percentage ?? ($iva_rate_system ?? 16.00)) }}" class="form-control-sm text-right @error('tax_percentage') is-invalid @enderror" style="width: 60px; display: inline-block;" step="0.01" min="0" required> %):</th>
                                    <td id="taxAmountText" class="text-right font-weight-bold">0.00</td>
                                </tr>
                                @error('tax_percentage') <tr><td colspan="2"><span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span></td></tr> @enderror
                                <tr style="font-size: 1.2em;" class="bg-light">
                                    <th class="pt-2 pb-2">Total ({{$quote->base_currency}}):</th>
                                    <td class="pt-2 pb-2"><strong id="quoteTotalText" class="text-right">0.00</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CAMPOS ADICIONALES --}}
                <hr class="mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="terms_and_conditions">Términos y Condiciones</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control @error('terms_and_conditions') is-invalid @enderror" rows="4">{{ old('terms_and_conditions', $default_terms_conditions_for_view ?? '') }}</textarea>
                            @error('terms_and_conditions') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control @error('notes_to_client') is-invalid @enderror" rows="4">{{ old('notes_to_client', $quote->notes_to_client) }}</textarea>
                            @error('notes_to_client') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                </div>
                 <div class="form-group">
                    <label for="internal_notes">Notas Internas (No visibles para el cliente)</label>
                    <textarea name="internal_notes" id="internal_notes" class="form-control @error('internal_notes') is-invalid @enderror" rows="2">{{ old('internal_notes', $quote->internal_notes) }}</textarea>
                    @error('internal_notes') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                </div>

            </div> {{-- /.card-body --}}
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Cotización</button>
                <a href="{{ route('quotes.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                 <button type="button" id="autoSaveTriggerButton" class="btn btn-info float-right" title="Autoguardar Progreso">
                    <i class="fas fa-sync-alt"></i> Autoguardar
                </button>
            </div>
        </form>
    </div> {{-- /.card --}}
@stop

@section('css')
    <style>
        #quoteItemsTable input[type="number"] { max-width: 120px; }
        #quoteItemsTable .form-check-label { font-size: 0.85rem; }
        .table-sm td, .table-sm th { padding: .4rem; }
    </style>
@stop

@section('js')
    <script>
    $(document).ready(function() {
        let BCV_RATE = parseFloat("{{ $bcv_rate ?? 0 }}");
        let PROMEDIO_RATE = parseFloat("{{ $promedio_rate ?? 0 }}");
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('restore_autosave') && urlParams.get('restore_autosave') === 'true') {
            const autosaveDataJson = @json($quote->auto_save_data ?? null);
            if (autosaveDataJson) {
                if (confirm("Hay datos autoguardados para esta cotización. ¿Desea restaurarlos?")) {
                    populateFormWithAutosaveData(autosaveDataJson);
                }
            }
        }

        function formatCurrency(value) {
            value = parseFloat(value);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        }

        function addNewItemRow(itemData = null) {
            let template = $('#quoteItemTemplate').html();
            let newRowIndex = 0;
            $('#quoteItemsTbody tr').each(function() {
                let nameAttr = $(this).find('input[name^="items["]').first().attr('name');
                if (nameAttr) {
                    let currentIndex = parseInt(nameAttr.match(/items\[(\d+)\]/)[1]);
                    if (currentIndex >= newRowIndex) newRowIndex = currentIndex + 1;
                }
            });
            template = template.replace(/__INDEX__/g, newRowIndex);
            let $newRow = $(template);

            if (itemData) {
                $newRow.find('input[name$="[id]"]').val(itemData.id || '');
                $newRow.find('.item-name').val(itemData.manual_product_name || '');
                $newRow.find('.item-quantity').val(itemData.quantity || 1);
                $newRow.find('.item-cost').val(formatCurrency(itemData.cost || 0));
                $newRow.find('.item-price').val(formatCurrency(itemData.price || 0));
                $newRow.find('.item-calc-method-hidden').val(itemData.price_calculation_method || '');
                $newRow.find('.item-applied-rate-hidden').val(itemData.applied_rate_value || '');
                if (itemData.price_calculation_method === 'promedio') $newRow.find('input[value="promedio"]').prop('checked', true).trigger('change');
                else if (itemData.price_calculation_method === 'bcv') $newRow.find('input[value="bcv"]').prop('checked', true).trigger('change');
                else $newRow.find('.item-price').prop('readonly', false);
            }

            $('#quoteItemsTbody').append($newRow);
            $newRow.find('input[type="checkbox"]').each(function() {
                let oldId = $(this).attr('id'); $(this).attr('id', oldId.replace('__INDEX__', newRowIndex));
                $(this).next('label').attr('for', $(this).attr('id'));
            });
            attachItemEventListenersToRow($newRow);
            calculateAll();
            if (!itemData) $newRow.find('.item-name').focus();
        }
        
        function populateFormWithAutosaveData(data) {
            if (!data || typeof data !== 'object') return;
            Object.keys(data).forEach(key => {
                if (key !== 'items' && key !== '_token' && key !== '_method') {
                    $('#' + key + ', [name="' + key + '"]').val(data[key]);
                }
            });
            $('#quoteItemsTbody').empty(); $('#deletedItemsContainer').empty();
            if (data.items && Array.isArray(data.items)) {
                data.items.forEach(itemData => addNewItemRow(itemData));
            }
            calculateAll();
            alert("Datos autoguardados restaurados.");
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('restore_autosave');
            window.history.replaceState({}, document.title, currentUrl.pathname + currentUrl.search);
        }

        $('#addManualItemButton').on('click', addNewItemRow);

        $('#quoteItemsTbody').on('click', '.removeItemButton', function() {
            let $row = $(this).closest('tr');
            let itemId = $row.find('input[name$="[id]"]').val(); 
            if (itemId && itemId !== '') {
                 if ($('#deletedItemsContainer').find('input[name="deleted_items[]"][value="' + itemId + '"]').length === 0) {
                    $('#deletedItemsContainer').append('<input type="hidden" name="deleted_items[]" value="' + itemId + '">');
                }
            }
            $row.remove();
            calculateAll();
        });

        function attachItemEventListenersToRow($row) {
            $row.find('.item-quantity, .item-cost, .item-price').off('.quoteitem').on('input.quoteitem change.quoteitem', function() {
                let $currentRow = $(this).closest('tr');
                if (!($(this).hasClass('item-price') && $currentRow.find('.item-price').prop('readonly'))) {
                    let quantity = parseFloat($currentRow.find('.item-quantity').val()) || 0;
                    let price = parseFloat($currentRow.find('.item-price').val()) || 0;
                    $currentRow.find('.item-line-total').text(formatCurrency(quantity * price));
                }
                calculateAll();
            });
            $row.find('.item-calc-method').off('.quoteitem').on('change.quoteitem', function() {
                let $currentRow = $(this).closest('tr'), $cb = $(this), method = $cb.val(), cost = parseFloat($currentRow.find('.item-cost').val()) || 0;
                let $priceIn = $currentRow.find('.item-price'), $methodHid = $currentRow.find('.item-calc-method-hidden'), $rateHid = $currentRow.find('.item-applied-rate-hidden');
                $currentRow.find('.item-calc-method').not($cb).prop('checked', false);
                if ($cb.is(':checked')) {
                    $methodHid.val(method); let rate = 0, valid = false;
                    if (method === 'promedio' && PROMEDIO_RATE > 0) { rate = PROMEDIO_RATE; valid = true; }
                    else if (method === 'bcv' && BCV_RATE > 0) { rate = BCV_RATE; valid = true; }
                    if (!valid) {
                        alert('Tasa para ' + method.toUpperCase() + ' no configurada o es cero.'); $cb.prop('checked', false);
                        $methodHid.val(''); $rateHid.val(''); $priceIn.prop('readonly', false).trigger('change.quoteitem'); return;
                    }
                    $priceIn.val(formatCurrency(cost * rate)).prop('readonly', true); $rateHid.val(rate.toFixed(4));
                } else {
                    $methodHid.val(''); $rateHid.val(''); $priceIn.prop('readonly', false);
                }
                $priceIn.trigger('change.quoteitem');
            });
        }
        
        $('#quoteItemsTbody tr').each(function() { attachItemEventListenersToRow($(this)); });
        calculateAll(); // Initial calculation

        // Autosave Logic
        let autosaveTimeout; const AUTOSAVE_DELAY = 30000;
        $('#quoteForm :input:not(#autoSaveTriggerButton)').on('input change keyup paste', () => { clearTimeout(autosaveTimeout); autosaveTimeout = setTimeout(() => triggerAutosave(false), AUTOSAVE_DELAY); });
        $('#autoSaveTriggerButton').on('click', () => triggerAutosave(true));

        function triggerAutosave(showAlert = false) {
            const quoteIdForAutosave = "{{ $quote->id ?? null }}"; 
            if (!quoteIdForAutosave) { if(showAlert) alert('Guarde la cotización como borrador primero para activar el autoguardado.'); return; }
            let formDataObject = {}; $('#quoteForm').serializeArray().forEach(item => { formDataObject[item.name] = item.value; });
            let itemsArray = []; let itemIndices = new Set();
            $('input[name^="items["]').each(function() { let match = $(this).attr('name').match(/items\[(\d+)\]/); if (match && match[1]) itemIndices.add(match[1]); });
            itemIndices.forEach(index => { let item = {}; $('input[name^="items[' + index + ']"], select[name^="items[' + index + ']"]').each(function() { let fieldNameMatch = $(this).attr('name').match(/items\[\d+\]\[(.*?)\]/); if (fieldNameMatch && fieldNameMatch[1]) item[fieldNameMatch[1]] = $(this).val(); }); if (Object.keys(item).length > 0) itemsArray.push(item); });
            formDataObject.items = itemsArray; delete formDataObject['calc_promedio_display']; delete formDataObject['calc_bcv_display']; // Eliminar campos de display

            $.ajax({
                url: "{{ route('quotes.autosave', ['quote' => $quote->id ?? 0]) }}", method: 'POST',
                data: { _token: "{{ csrf_token() }}", ...formDataObject },
                beforeSend: () => $('#autoSaveTriggerButton').prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Guardando...'),
                success: res => { if (showAlert) alert(res.message || 'Progreso autoguardado.'); console.log('Autosave successful:', res); },
                error: xhr => { let msg = xhr.responseJSON?.message || 'Error al autoguardar.'; if (showAlert) alert(msg); console.error('Autosave error:', xhr); },
                complete: () => $('#autoSaveTriggerButton').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Autoguardar')
            });
        }
    });
    </script>
@stop