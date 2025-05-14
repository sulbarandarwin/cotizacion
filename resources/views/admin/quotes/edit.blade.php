@extends('adminlte::page')

@section('title', ($is_show_mode ?? false) ? ('Ver Cotización #' . $quote->quote_number) : ('Editar Cotización #' . $quote->quote_number))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">{{ ($is_show_mode ?? false) ? 'Ver Cotización' : 'Editar Cotización' }} #{{ $quote->quote_number }}</h1>
        <a href="{{ route('quotes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a la Lista</a>
    </div>
@stop

@section('content')
    <div class="card {{ ($is_show_mode ?? false) ? 'card-info' : 'card-warning' }} card-outline">
        <div class="card-header">
            <h3 class="card-title">Información General y Cliente</h3>
        </div>
        <form action="{{ route('quotes.update', $quote->id) }}" method="POST" id="quoteForm">
            @if(!($is_show_mode ?? false))
                @method('PUT')
            @endif
            @csrf
            <div class="card-body">
                {{-- Fila para Cliente y Fechas --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="client_id">Cliente <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-control @error('client_id') is-invalid @enderror" {{ ($is_show_mode ?? false) ? 'disabled' : '' }} required>
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
                            <label for="status_display">Estado</label> {{-- Cambiado a status_display para evitar conflicto con un posible input de status --}}
                            <input type="text" id="status_display" class="form-control" value="{{ $quote->status }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="issue_date">Fecha de Emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $quote->issue_date->format('Y-m-d')) }}" {{ ($is_show_mode ?? false) ? 'readonly' : '' }} required>
                            @error('issue_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expiry_date">Fecha de Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', $quote->expiry_date->format('Y-m-d')) }}" {{ ($is_show_mode ?? false) ? 'readonly' : '' }} required>
                            @error('expiry_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE ÍTEMS DE LA COTIZACIÓN --}}
                <hr>
                <div class="row mt-3 mb-1">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h4>Ítems de la Cotización</h4>
                        @if(!($is_show_mode ?? false)) {{-- Solo mostrar botón si no es modo show --}}
                        <button type="button" id="addManualItemButton" class="btn btn-info btn-sm"><i class="fas fa-plus"></i> Añadir Ítem</button>
                        @endif
                    </div>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-bordered table-hover" id="quoteItemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Producto/Servicio <span class="text-danger">*</span></th>
                                <th style="width: 10%;" class="text-center">Cantidad <span class="text-danger">*</span></th>
                                <th style="width: 13%;" class="text-center">Costo Unit. ($) <span class="text-danger">*</span></th>
                                <th style="width: 22%;" class="text-center">Cálculo Precio</th>
                                <th style="width: 13%;" class="text-center">Precio Unit. ($) <span class="text-danger">*</span></th>
                                <th style="width: 12%;" class="text-center">Total Línea ($)</th>
                                @if(!($is_show_mode ?? false)) {{-- Solo mostrar columna de acción si no es modo show --}}
                                <th style="width: 5%;" class="text-center">Acción</th>
                                @endif
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
                                        'price' => $item->price,
                                    ];
                                })->toArray());
                            @endphp

                            @if(is_array($itemsToDisplay) && count($itemsToDisplay) > 0)
                                @foreach($itemsToDisplay as $key => $itemData)
                                    <tr>
                                        <td>
                                            @if(isset($itemData['id']) && !($is_show_mode ?? false)) {{-- Solo enviar ID si no es show mode --}}
                                                <input type="hidden" name="items[{{ $key }}][id]" value="{{ $itemData['id'] }}">
                                            @endif
                                            <input type="text" name="items[{{ $key }}][manual_product_name]" class="form-control item-name" value="{{ $itemData['manual_product_name'] ?? '' }}" placeholder="Nombre del producto/servicio" {{ ($is_show_mode ?? false) ? 'readonly' : '' }} required>
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][quantity]" class="form-control item-quantity text-right" value="{{ $itemData['quantity'] ?? 1 }}" min="0.01" step="any" {{ ($is_show_mode ?? false) ? 'readonly' : '' }} required></td>
                                        <td><input type="number" name="items[{{ $key }}][cost]" class="form-control item-cost text-right" value="{{ number_format((float)($itemData['cost'] ?? 0.00), 2, '.', '') }}" min="0" step="any" {{ ($is_show_mode ?? false) ? 'readonly' : '' }} required></td>
                                        <td class="text-center">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_promedio]" id="calc_promedio_{{ $key }}" value="promedio" {{ (isset($itemData['price_calculation_method']) && $itemData['price_calculation_method'] == 'promedio') ? 'checked' : '' }} {{ ($is_show_mode ?? false) ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="calc_promedio_{{ $key }}">Promedio</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_bcv]" id="calc_bcv_{{ $key }}" value="bcv" {{ (isset($itemData['price_calculation_method']) && $itemData['price_calculation_method'] == 'bcv') ? 'checked' : '' }} {{ ($is_show_mode ?? false) ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="calc_bcv_{{ $key }}">BCV</label>
                                            </div>
                                            <input type="hidden" name="items[{{ $key }}][price_calculation_method]" class="item-calc-method-hidden" value="{{ $itemData['price_calculation_method'] ?? '' }}">
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][price]" class="form-control item-price text-right" value="{{ number_format((float)($itemData['price'] ?? 0.00), 2, '.', '') }}" min="0" step="any" {{ ($is_show_mode ?? false) || (isset($itemData['price_calculation_method']) && $itemData['price_calculation_method'] != '') ? 'readonly' : '' }} required></td>
                                        <td class="item-line-total text-right font-weight-bold">0.00</td>
                                        @if(!($is_show_mode ?? false))
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeItemButton"><i class="fas fa-trash"></i></button></td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </template>
                @if(!($is_show_mode ?? false)) {{-- Solo mostrar template si no es modo show --}}
                <template id="quoteItemTemplate">
                    <tr>
                        <td>
                            <input type="text" name="items[__INDEX__][manual_product_name]" class="form-control item-name" placeholder="Nombre del producto/servicio" required>
                        </td>
                        <td><input type="number" name="items[__INDEX__][quantity]" class="form-control item-quantity text-right" value="1" min="0.01" step="any" required></td>
                        <td><input type="number" name="items[__INDEX__][cost]" class="form-control item-cost text-right" value="0.00" min="0" step="any" required></td>
                        <td class="text-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_promedio]" id="calc_promedio___INDEX__" value="promedio">
                                <label class="form-check-label" for="calc_promedio___INDEX__">Promedio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_bcv]" id="calc_bcv___INDEX__" value="bcv">
                                <label class="form-check-label" for="calc_bcv___INDEX__">BCV</label>
                            </div>
                             <input type="hidden" name="items[__INDEX__][price_calculation_method]" class="item-calc-method-hidden" value="">
                        </td>
                        <td><input type="number" name="items[__INDEX__][price]" class="form-control item-price text-right" value="0.00" min="0" step="any" required></td>
                        <td class="item-line-total text-right font-weight-bold">0.00</td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeItemButton"><i class="fas fa-trash"></i></button></td>
                    </tr>
                </template>
                @endif

                <hr class="mt-4">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th style="width:50%">Subtotal:</th>
                                    <td id="quoteSubtotalText" class="text-right font-weight-bold"></td>
                                    @if(!($is_show_mode ?? false))<input type="hidden" name="subtotal" id="quoteSubtotalInput" value="{{ old('subtotal', $quote->subtotal) }}">@endif
                                </tr>
                                <tr>
                                    <th>Descuento Global:</th>
                                    <td class="text-right">
                                        <div class="input-group input-group-sm" style="width: 200px; float: right;">
                                            <input type="number" name="discount_value" id="discount_value" class="form-control text-right" value="{{ old('discount_value', $quote->discount_value ?? 0) }}" min="0" step="any" {{ ($is_show_mode ?? false) ? 'readonly' : '' }}>
                                            <div class="input-group-append">
                                                <select name="discount_type" id="discount_type" class="form-control" {{ ($is_show_mode ?? false) ? 'disabled' : '' }}>
                                                    <option value="fixed" {{ old('discount_type', $quote->discount_type) == 'fixed' ? 'selected' : '' }}>Monto ($)</option>
                                                    <option value="percentage" {{ old('discount_type', $quote->discount_type) == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Monto Descuento (-):</th>
                                    <td id="discountAmountText" class="text-right text-danger font-weight-bold"></td>
                                    @if(!($is_show_mode ?? false))<input type="hidden" name="discount_amount" id="discountAmountInput" value="{{ old('discount_amount', $quote->discount_amount) }}">@endif
                                </tr>
                                <tr>
                                    <th>Base Imponible:</th>
                                    <td id="taxableBaseText" class="text-right font-weight-bold"></td>
                                </tr>
                                <tr>
                                    <th>IVA (<span id="ivaRateSpan">{{ number_format(old('tax_percentage', $quote->tax_percentage ?? ($iva_rate ?? 16)), 2) }}</span>%):</th>
                                    <td id="taxAmountText" class="text-right font-weight-bold"></td>
                                    @if(!($is_show_mode ?? false))
                                    <input type="hidden" name="tax_percentage" id="taxPercentageInput" value="{{ old('tax_percentage', $quote->tax_percentage ?? ($iva_rate ?? 16.00)) }}">
                                    <input type="hidden" name="tax_amount" id="taxAmountInput" value="{{ old('tax_amount', $quote->tax_amount) }}">
                                    @else
                                    <input type="hidden" id="taxPercentageInput" value="{{ old('tax_percentage', $quote->tax_percentage ?? ($iva_rate ?? 16.00)) }}"> {{-- Para que el JS lo lea --}}
                                    @endif
                                </tr>
                                <tr style="font-size: 1.2em;" class="bg-light">
                                    <th class="pt-2 pb-2">Total ($):</th>
                                    <td class="pt-2 pb-2"><strong id="quoteTotalText" class="text-right"></strong></td>
                                    @if(!($is_show_mode ?? false))<input type="hidden" name="total" id="quoteTotalInput" value="{{ old('total', $quote->total) }}">@endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="terms_and_conditions">Términos y Condiciones</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control" rows="4" {{ ($is_show_mode ?? false) ? 'readonly' : '' }}>{{ old('terms_and_conditions', $quote->terms_and_conditions) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control" rows="4" {{ ($is_show_mode ?? false) ? 'readonly' : '' }}>{{ old('notes_to_client', $quote->notes_to_client) }}</textarea>
                        </div>
                    </div>
                </div>
                 <div class="form-group">
                    <label for="internal_notes">Notas Internas</label>
                    <textarea name="internal_notes" id="internal_notes" class="form-control" rows="2" {{ ($is_show_mode ?? false) ? 'readonly' : '' }}>{{ old('internal_notes', $quote->internal_notes) }}</textarea>
                </div>

            </div>
            <div class="card-footer">
                @if(!($is_show_mode ?? false))
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Cotización</button>
                @endif
                <a href="{{ route('quotes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> {{ ($is_show_mode ?? false) ? 'Volver a la Lista' : 'Cancelar' }}</a>
            </div>
        </form>
    </div>
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
        let isShowMode = {{ ($is_show_mode ?? false) ? 'true' : 'false' }};

        let IVA_RATE = parseFloat($('#taxPercentageInput').val()) || 16.00;
        let BCV_RATE = parseFloat("{{ $bcv_rate ?? 0 }}");
        let PROMEDIO_RATE = parseFloat("{{ $promedio_rate ?? 0 }}");
        $('#ivaRateSpan').text(IVA_RATE.toFixed(2));
        let itemIndex = $('#quoteItemsTbody tr').length;

        function formatCurrency(value) {
            value = parseFloat(value);
            if (isNaN(value)) value = 0;
            return value.toFixed(2);
        }

        function addNewItemRow() {
            if (isShowMode) return;
            let template = $('#quoteItemTemplate').html();
            let newIndexForTemplate = itemIndex;
            template = template.replace(/__INDEX__/g, newIndexForTemplate);
            let $newRow = $(template);

            $newRow.find('.item-name').val('');
            $newRow.find('.item-cost').val('0.00').prop('readonly', false);
            $newRow.find('.item-price').val('0.00').prop('readonly', false);
            $newRow.find('.item-calc-method').prop('checked', false);
            $newRow.find('.item-calc-method-hidden').val('');

            $('#quoteItemsTbody').append($newRow);
            $newRow.find('input[type="checkbox"]').each(function() {
                let oldId = $(this).attr('id');
                let newId = oldId.replace('__INDEX__', newIndexForTemplate);
                $(this).attr('id', newId);
                $(this).next('label').attr('for', newId);
            });
            itemIndex++;
            attachItemEventListeners();
            calculateAll();
            $newRow.find('.item-name').focus();
        }

        if (!isShowMode) {
            $('#addManualItemButton').on('click', addNewItemRow);
        }

        $('#quoteItemsTbody').on('click', '.removeItemButton', function() {
            if (isShowMode) return;
            $(this).closest('tr').remove();
            let itemId = $(this).closest('tr').find('input[name$="[id]"]').val();
            if (itemId) { // Si es un item que ya existía y tiene ID
                 // Añadir a un campo oculto para que el backend sepa que debe borrarlo
                $('#quoteForm').append('<input type="hidden" name="deleted_items[]" value="' + itemId + '">');
            }
            calculateAll();
        });

        function attachItemEventListeners() {
            $('.item-quantity, .item-cost, .item-price').off('.quoteitem').on('input.quoteitem change.quoteitem', function() {
                if (isShowMode && !$(this).hasClass('item-calc-method')) return;
                let $row = $(this).closest('tr');
                let isPriceField = $(this).hasClass('item-price');
                let isPriceReadonly = $row.find('.item-price').prop('readonly');

                if (isPriceField && isPriceReadonly && !$(this).hasClass('item-calc-method')) { /* No hacer nada */ }
                else {
                    let quantity = parseFloat($row.find('.item-quantity').val()) || 0;
                    let price = parseFloat($row.find('.item-price').val()) || 0;
                    $row.find('.item-line-total').text(formatCurrency(quantity * price));
                }
                calculateAll();
            });

            $('.item-calc-method').off('.quoteitem').on('change.quoteitem', function() {
                if (isShowMode) return;
                let $row = $(this).closest('tr');
                let $checkboxChanged = $(this);
                let methodName = $checkboxChanged.val();
                let cost = parseFloat($row.find('.item-cost').val()) || 0;
                let $itemPriceInput = $row.find('.item-price');
                let $hiddenMethodInput = $row.find('.item-calc-method-hidden');

                $row.find('.item-calc-method').not($checkboxChanged).prop('checked', false);

                if ($checkboxChanged.is(':checked')) {
                    $hiddenMethodInput.val(methodName);
                    let rate = 0;
                    let validRate = true;
                    if (methodName === 'promedio') { rate = PROMEDIO_RATE; if (PROMEDIO_RATE <= 0) validRate = false; }
                    else if (methodName === 'bcv') { rate = BCV_RATE; if (BCV_RATE <= 0) validRate = false; }

                    if (!validRate) {
                        alert('La tasa para ' + methodName.toUpperCase() + ' no está configurada o es cero.');
                        $checkboxChanged.prop('checked', false);
                        $hiddenMethodInput.val('');
                        $itemPriceInput.prop('readonly', false);
                        $itemPriceInput.trigger('change');
                        return;
                    }
                    $itemPriceInput.val( (cost * rate).toFixed(2) );
                    $itemPriceInput.prop('readonly', true);
                } else {
                    $hiddenMethodInput.val('');
                    $itemPriceInput.prop('readonly', false);
                }
                $itemPriceInput.trigger('change');
            });
        }

        function calculateAll() {
            let subtotal = 0;
            $('#quoteItemsTbody tr').each(function() {
                let quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                let price = parseFloat($(this).find('.item-price').val()) || 0;
                let lineTotal = quantity * price;
                $(this).find('.item-line-total').text(formatCurrency(lineTotal));
                subtotal += lineTotal;
            });

            $('#quoteSubtotalText').text(formatCurrency(subtotal));
            if (!isShowMode) $('#quoteSubtotalInput').val(subtotal.toFixed(2));

            let discountValue = parseFloat($('#discount_value').val()) || 0;
            let discountType = $('#discount_type').val();
            let discountAmount = (discountType === 'percentage') ? (subtotal * discountValue) / 100 : discountValue;
            discountAmount = Math.min(discountAmount, subtotal);

            $('#discountAmountText').text(formatCurrency(discountAmount));
            if (!isShowMode) $('#discountAmountInput').val(discountAmount.toFixed(2));

            let taxableBase = subtotal - discountAmount;
            $('#taxableBaseText').text(formatCurrency(taxableBase));

            let current_iva_rate = parseFloat($('#taxPercentageInput').val()) || 0;
            let taxAmount = (taxableBase * current_iva_rate) / 100;
            $('#taxAmountText').text(formatCurrency(taxAmount));
            if (!isShowMode) $('#taxAmountInput').val(taxAmount.toFixed(2));

            let total = taxableBase + taxAmount;
            $('#quoteTotalText').text(formatCurrency(total));
            if (!isShowMode) $('#quoteTotalInput').val(total.toFixed(2));
        }

        if (!isShowMode) {
            $('#discount_value, #discount_type').on('input change', calculateAll);
            $('#taxPercentageInput').on('input change', function() { // Si permites cambiar la tasa de IVA en el formulario
                IVA_RATE = parseFloat($(this).val()) || 0;
                $('#ivaRateSpan').text(IVA_RATE.toFixed(2));
                calculateAll();
            });
        }

        attachItemEventListeners();
        calculateAll();

        if (isShowMode) {
            $('#quoteForm :input:not(.btn-secondary)').prop('disabled', true); // Deshabilitar todo excepto el botón de cancelar/volver
            // $('.removeItemButton').hide(); // Los botones de eliminar ya están condicionados en el HTML
            // $('#addManualItemButton').hide(); // Ya está condicionado en el HTML
        }
    });
    </script>
@stop