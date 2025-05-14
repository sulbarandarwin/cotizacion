@extends('adminlte::page')

@section('title', 'Crear Nueva Cotización')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Cotización</h1>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Información General y Cliente</h3>
        </div>
        <form action="{{ route('quotes.store') }}" method="POST" id="quoteForm">
            @csrf
            <div class="card-body">
                {{-- Fila para Cliente y Fechas --}}
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="client_id">Cliente <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente...</option>
                                @if(isset($clients) && $clients->count() > 0)
                                    @foreach($clients as $id => $name)
                                        <option value="{{ $id }}" {{ old('client_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No hay clientes disponibles</option>
                                @endif
                            </select>
                            @error('client_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        {{-- Espacio o campo adicional --}}
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="issue_date">Fecha de Emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', now()->format('Y-m-d')) }}" required>
                            @error('issue_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="expiry_date">Fecha de Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', now()->addDays(isset($default_validity_days) ? (int)$default_validity_days : 15)->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE ÍTEMS DE LA COTIZACIÓN --}}
                <hr>
                <div class="row mt-3 mb-1">
                    <div class="col-md-12">
                        <h4>Ítems de la Cotización</h4>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        {{-- Campo de búsqueda de productos (COMENTADO TEMPORALMENTE) --}}
                        {{--
                        <label for="productSearchInput">Buscar Producto por Nombre o Código:</label>
                        <input type="text" id="productSearchInput" class="form-control form-control-lg" placeholder="Escriba para buscar...">
                        <div id="productSearchResults" class="list-group mt-1" style="position: absolute; z-index: 1000; width: 95%; max-height: 300px; overflow-y: auto;">
                        </div>
                        --}}
                    </div>
                    <div class="col-md-4 text-right align-self-end">
                        <button type="button" id="addManualItemButton" class="btn btn-info"><i class="fas fa-plus"></i> Añadir Ítem</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover" id="quoteItemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Producto/Servicio <span class="text-danger">*</span></th>
                                <th style="width: 10%;" class="text-center">Cantidad <span class="text-danger">*</span></th>
                                <th style="width: 13%;" class="text-center">Costo Unit. ($) <span class="text-danger">*</span></th>
                                <th style="width: 22%;" class="text-center">Cálculo Precio</th>
                                <th style="width: 13%;" class="text-center">Precio Unit. ($) <span class="text-danger">*</span></th>
                                <th style="width: 12%;" class="text-center">Total Línea ($)</th>
                                <th style="width: 5%;" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="quoteItemsTbody">
                            @if(is_array(old('items')))
                                @foreach(old('items') as $key => $oldItem)
                                    <tr>
                                        <td>
                                            <input type="text" name="items[{{ $key }}][manual_product_name]" class="form-control item-name" value="{{ $oldItem['manual_product_name'] ?? '' }}" placeholder="Nombre del producto/servicio" required>
                                            {{-- <input type="hidden" name="items[{{ $key }}][product_id]" class="item-product-id" value=""> --}}
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][quantity]" class="form-control item-quantity text-right" value="{{ $oldItem['quantity'] ?? 1 }}" min="0.01" step="any" required></td>
                                        <td><input type="number" name="items[{{ $key }}][cost]" class="form-control item-cost text-right" value="{{ $oldItem['cost'] ?? 0.00 }}" min="0" step="any" required></td>
                                        <td class="text-center">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_promedio]" id="calc_promedio_{{ $key }}" value="promedio" {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] == 'promedio') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_promedio_{{ $key }}">Promedio</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_bcv]" id="calc_bcv_{{ $key }}" value="bcv" {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] == 'bcv') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_bcv_{{ $key }}">BCV</label>
                                            </div>
                                            <input type="hidden" name="items[{{ $key }}][price_calculation_method]" class="item-calc-method-hidden" value="{{ $oldItem['price_calculation_method'] ?? '' }}">
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][price]" class="form-control item-price text-right" value="{{ $oldItem['price'] ?? 0.00 }}" min="0" step="any" required {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] != '') ? 'readonly' : '' }}></td>
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
                            {{-- <input type="hidden" name="items[__INDEX__][product_id]" class="item-product-id" value=""> --}}
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

                {{-- SECCIÓN DE TOTALES --}}
                <hr class="mt-4">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th style="width:50%">Subtotal:</th>
                                    <td id="quoteSubtotalText" class="text-right font-weight-bold">0.00</td>
                                    <input type="hidden" name="subtotal" id="quoteSubtotalInput" value="0">
                                </tr>
                                <tr>
                                    <th>Descuento Global:</th>
                                    <td class="text-right">
                                        <div class="input-group input-group-sm" style="width: 200px; float: right;">
                                            <input type="number" name="discount_value" id="discount_value" class="form-control text-right" value="{{ old('discount_value', 0) }}" min="0" step="any">
                                            <div class="input-group-append">
                                                <select name="discount_type" id="discount_type" class="form-control">
                                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Monto ($)</option>
                                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Monto Descuento (-):</th>
                                    <td id="discountAmountText" class="text-right text-danger font-weight-bold">0.00</td>
                                    <input type="hidden" name="discount_amount" id="discountAmountInput" value="0">
                                </tr>
                                <tr>
                                    <th>Base Imponible:</th>
                                    <td id="taxableBaseText" class="text-right font-weight-bold">0.00</td>
                                </tr>
                                <tr>
                                    <th>IVA (<span id="ivaRateSpan">{{ number_format( $iva_rate ?? 16, 2) }}</span>%):</th>
                                    <td id="taxAmountText" class="text-right font-weight-bold">0.00</td>
                                    <input type="hidden" name="tax_percentage" id="taxPercentageInput" value="{{ $iva_rate ?? 16.00 }}">
                                    <input type="hidden" name="tax_amount" id="taxAmountInput" value="0">
                                </tr>
                                <tr style="font-size: 1.2em;" class="bg-light">
                                    <th class="pt-2 pb-2">Total ($):</th>
                                    <td class="pt-2 pb-2"><strong id="quoteTotalText" class="text-right">0.00</strong></td>
                                    <input type="hidden" name="total" id="quoteTotalInput" value="0">
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
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control" rows="4">{{ old('terms_and_conditions', $default_terms_conditions ?? "1. Validez de la oferta: 15 días.\n2. Precios sujetos a cambio sin previo aviso.\n3. No se aceptan devoluciones después de X días.") }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control" rows="4">{{ old('notes_to_client') }}</textarea>
                        </div>
                    </div>
                </div>
                 <div class="form-group">
                    <label for="internal_notes">Notas Internas (No visibles para el cliente)</label>
                    <textarea name="internal_notes" id="internal_notes" class="form-control" rows="2">{{ old('internal_notes') }}</textarea>
                </div>

            </div> {{-- /.card-body --}}
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cotización</button>
                <a href="{{ route('quotes.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <button type="button" id="autoSaveButton" class="btn btn-info float-right" style="display:none;"><i class="fas fa-sync-alt"></i> Guardando...</button>
            </div>
        </form>
    </div> {{-- /.card --}}
@stop

@section('css')
    <style>
        /* Ajustes menores para la tabla de ítems y totales */
        #quoteItemsTable input[type="number"] { max-width: 120px; }
        #quoteItemsTable .form-check-label { font-size: 0.85rem; }
        .table-sm td, .table-sm th { padding: .4rem; }
    </style>
@stop

@section('js')
    <script>
    $(document).ready(function() {
        // console.log('Formulario de creación de cotizaciones v4 (cálculo subtotal corregido).');

        // --- INICIALIZACIÓN DE VARIABLES Y VALORES GLOBALES ---
        let IVA_RATE = parseFloat($('#taxPercentageInput').val()) || 16.00;
        // Estas tasas se obtendrán del sistema, por ahora son placeholders o de las variables PHP
        let BCV_RATE = parseFloat("{{ $bcv_rate ?? 0 }}");
        let PROMEDIO_RATE = parseFloat("{{ $promedio_rate ?? 0 }}");

        $('#ivaRateSpan').text(IVA_RATE.toFixed(2));

        let itemIndex = $('#quoteItemsTbody tr').length > 0 ? $('#quoteItemsTbody tr').length : 0;

        // --- FUNCIONES AUXILIARES ---
        function formatCurrency(value) {
            value = parseFloat(value);
            if (isNaN(value)) {
                value = 0;
            }
            // Devuelve el número con 2 decimales, usando punto como separador decimal.
            // El formateo con separador de miles se puede hacer al mostrar, no al calcular.
            return value.toFixed(2);
        }

        // --- MANEJO DE ÍTEMS ---
        function addNewItemRow() { // Eliminamos productData ya que no hay búsqueda AJAX por ahora
            let template = $('#quoteItemTemplate').html();
            template = template.replace(/__INDEX__/g, itemIndex);
            let $newRow = $(template);

            // Limpiar campos para ítem manual
            $newRow.find('.item-name').val('');
            // $newRow.find('.item-product-id').val(''); // No hay product_id para manuales
            // $newRow.find('.item-sku').text('');
            $newRow.find('.item-cost').val('0.00').prop('readonly', false);
            $newRow.find('.item-price').val('0.00').prop('readonly', false);
            $newRow.find('.item-calc-method').prop('checked', false);
            $newRow.find('.item-calc-method-hidden').val('');


            $('#quoteItemsTbody').append($newRow);
            $newRow.find('input[type="checkbox"]').each(function() {
                let oldId = $(this).attr('id');
                let newId = oldId.replace('__INDEX__', itemIndex);
                $(this).attr('id', newId);
                $(this).next('label').attr('for', newId);
            });
            itemIndex++;
            attachItemEventListeners();
            calculateAll();
            $newRow.find('.item-name').focus();
        }

        $('#addManualItemButton').on('click', function() {
            addNewItemRow();
        });

        $('#quoteItemsTbody').on('click', '.removeItemButton', function() {
            $(this).closest('tr').remove();
            calculateAll();
        });

        function attachItemEventListeners() {
            $('.item-quantity, .item-cost, .item-price').off('.quoteitem').on('input.quoteitem change.quoteitem', function() {
                let $row = $(this).closest('tr');
                let isPriceField = $(this).hasClass('item-price');
                let isPriceReadonly = $row.find('.item-price').prop('readonly');

                if (isPriceField && isPriceReadonly) { /* No hacer nada */ }
                else {
                    let quantity = parseFloat($row.find('.item-quantity').val()) || 0;
                    let price = parseFloat($row.find('.item-price').val()) || 0;
                    $row.find('.item-line-total').text(formatCurrency(quantity * price));
                }
                calculateAll();
            });

            $('.item-calc-method').off('.quoteitem').on('change.quoteitem', function() {
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

        // --- CÁLCULO DE TOTALES ---
        function calculateAll() {
            let subtotal = 0;
            $('#quoteItemsTbody tr').each(function() {
                let quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                let price = parseFloat($(this).find('.item-price').val()) || 0;
                let lineTotal = quantity * price; // <<< SE USA EL VALOR NUMÉRICO DIRECTAMENTE
                $(this).find('.item-line-total').text(formatCurrency(lineTotal));
                subtotal += lineTotal; // <<< CORRECCIÓN APLICADA AQUÍ
            });

            $('#quoteSubtotalText').text(formatCurrency(subtotal));
            $('#quoteSubtotalInput').val(subtotal.toFixed(2));

            let discountValue = parseFloat($('#discount_value').val()) || 0;
            let discountType = $('#discount_type').val();
            let discountAmount = (discountType === 'percentage') ? (subtotal * discountValue) / 100 : discountValue;
            discountAmount = Math.min(discountAmount, subtotal);

            $('#discountAmountText').text(formatCurrency(discountAmount));
            $('#discountAmountInput').val(discountAmount.toFixed(2));

            let taxableBase = subtotal - discountAmount;
            $('#taxableBaseText').text(formatCurrency(taxableBase));

            let current_iva_rate = parseFloat($('#taxPercentageInput').val()) || 0;
            let taxAmount = (taxableBase * current_iva_rate) / 100;
            $('#taxAmountText').text(formatCurrency(taxAmount));
            $('#taxAmountInput').val(taxAmount.toFixed(2));

            let total = taxableBase + taxAmount;
            $('#quoteTotalText').text(formatCurrency(total));
            $('#quoteTotalInput').val(total.toFixed(2));
        }

        $('#discount_value, #discount_type').on('input change', calculateAll);
        $('#taxPercentageInput').on('input change', function() { // Si permites cambiar la tasa de IVA en el formulario
            IVA_RATE = parseFloat($(this).val()) || 0;
            $('#ivaRateSpan').text(IVA_RATE.toFixed(2));
            calculateAll();
        });


        // Llamada inicial para listeners y cálculos si hay ítems 'old'
        attachItemEventListeners();
        calculateAll();

        // Añadir una fila vacía si no hay ítems (ni 'old' ítems)
        if ($('#quoteItemsTbody tr').length === 0 && !@json(old('items'))) {
            $('#addManualItemButton').trigger('click');
        }

        // COMENTANDO LA PARTE DE BÚSQUEDA AJAX
        /*
        let searchRequest = null;
        $('#productSearchInput').on('keyup', function() {
            // ...código de búsqueda AJAX comentado...
        });

        $('#productSearchResults').on('click', 'a.list-group-item-action', function(e) {
            // ...código de clic en resultados comentado...
        });

        $(document).on('click', function(e) {
            // ...código de ocultar resultados comentado...
        });
        */
    });
    </script>
@stop