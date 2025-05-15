@extends('adminlte::page')

@section('title', 'Crear Nueva Cotización')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Cotización</h1>
@stop

@section('content')
    {{-- Mostrar errores de validación generales o de sesión --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error de Validación!</h5>
            Por favor, corrija los errores en el formulario.
             {{-- Opcional: listar errores:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            --}}
        </div>
    @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Información General y Cliente</h3>
        </div>
        <form action="{{ route('quotes.store') }}" method="POST" id="quoteForm">
            @csrf
            <div class="card-body">
                {{-- Fila para Cliente y Fechas --}}
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-2">
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
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expiry_date">Fecha de Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', now()->addDays($default_validity_days ?? 15)->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                 {{-- Moneda y Tasas --}}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="base_currency">Moneda Base <span class="text-danger">*</span></label>
                            <select name="base_currency" id="base_currency" class="form-control @error('base_currency') is-invalid @enderror" required>
                                <option value="USD" {{ old('base_currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="BS" {{ old('base_currency') == 'BS' ? 'selected' : '' }}>BS</option>
                            </select>
                            @error('base_currency') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_bcv">Tasa BCV (Opcional)</label>
                            <input type="number" name="exchange_rate_bcv" id="exchange_rate_bcv" class="form-control @error('exchange_rate_bcv') is-invalid @enderror" value="{{ old('exchange_rate_bcv', $bcv_rate > 0 ? $bcv_rate : '') }}" placeholder="{{ $bcv_rate > 0 ? $bcv_rate : '0.00' }}" step="any" min="0">
                            @error('exchange_rate_bcv') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_promedio">Tasa Promedio (Opcional)</label>
                            <input type="number" name="exchange_rate_promedio" id="exchange_rate_promedio" class="form-control @error('exchange_rate_promedio') is-invalid @enderror" value="{{ old('exchange_rate_promedio', $promedio_rate > 0 ? $promedio_rate : '') }}" placeholder="{{ $promedio_rate > 0 ? $promedio_rate : '0.00' }}" step="any" min="0">
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
                                            <input type="text" name="items[{{ $key }}][manual_product_name]" class="form-control item-name @error('items.'.$key.'.manual_product_name') is-invalid @enderror" value="{{ $oldItem['manual_product_name'] ?? '' }}" placeholder="Nombre del producto/servicio" required>
                                            @error('items.'.$key.'.manual_product_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][quantity]" class="form-control item-quantity text-right @error('items.'.$key.'.quantity') is-invalid @enderror" value="{{ $oldItem['quantity'] ?? 1 }}" min="0.01" step="any" required></td>
                                        <td><input type="number" name="items[{{ $key }}][cost]" class="form-control item-cost text-right @error('items.'.$key.'.cost') is-invalid @enderror" value="{{ number_format((float)($oldItem['cost'] ?? 0.00), 2, '.', '') }}" min="0" step="any" required></td>
                                        <td class="text-center">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_promedio]" id="calc_promedio_{{ $key }}" value="promedio" {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] == 'promedio') || (isset($oldItem['calc_promedio']) && $oldItem['calc_promedio'] == 'promedio') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_promedio_{{ $key }}">Promedio</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input item-calc-method" type="checkbox" name="items[{{ $key }}][calc_bcv]" id="calc_bcv_{{ $key }}" value="bcv" {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] == 'bcv') || (isset($oldItem['calc_bcv']) && $oldItem['calc_bcv'] == 'bcv') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calc_bcv_{{ $key }}">BCV</label>
                                            </div>
                                            <input type="hidden" name="items[{{ $key }}][price_calculation_method]" class="item-calc-method-hidden" value="{{ $oldItem['price_calculation_method'] ?? '' }}">
                                            <input type="hidden" name="items[{{ $key }}][applied_rate_value]" class="item-applied-rate-hidden" value="{{ $oldItem['applied_rate_value'] ?? '' }}">
                                        </td>
                                        <td><input type="number" name="items[{{ $key }}][price]" class="form-control item-price text-right @error('items.'.$key.'.price') is-invalid @enderror" value="{{ number_format((float)($oldItem['price'] ?? 0.00), 2, '.', '') }}" min="0" step="any" {{ (isset($oldItem['price_calculation_method']) && $oldItem['price_calculation_method'] != '') ? 'readonly' : '' }} required></td>
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
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_promedio]" id="calc_promedio___INDEX__" value="promedio">
                                <label class="form-check-label" for="calc_promedio___INDEX__">Promedio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input item-calc-method" type="checkbox" name="items[__INDEX__][calc_bcv]" id="calc_bcv___INDEX__" value="bcv">
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
                                            <input type="number" name="discount_value" id="discount_value" class="form-control text-right @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', 0) }}" min="0" step="any">
                                            <div class="input-group-append">
                                                <select name="discount_type" id="discount_type" class="form-control @error('discount_type') is-invalid @enderror">
                                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Monto ($)</option>
                                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
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
                                    <th>IVA (<input type="number" name="tax_percentage" id="taxPercentageInput" value="{{ old('tax_percentage', $iva_rate ?? 16.00) }}" class="form-control-sm text-right @error('tax_percentage') is-invalid @enderror" style="width: 60px; display: inline-block;" step="0.01" required> %):</th>
                                    <td id="taxAmountText" class="text-right font-weight-bold">0.00</td>
                                </tr>
                                @error('tax_percentage') <tr><td colspan="2"><span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span></td></tr> @enderror
                                <tr style="font-size: 1.2em;" class="bg-light">
                                    <th class="pt-2 pb-2">Total ($):</th>
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
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control @error('terms_and_conditions') is-invalid @enderror" rows="4">{{ old('terms_and_conditions', $default_terms_conditions ?? '') }}</textarea>
                             @error('terms_and_conditions') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control @error('notes_to_client') is-invalid @enderror" rows="4">{{ old('notes_to_client') }}</textarea>
                            @error('notes_to_client') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                </div>
                 <div class="form-group">
                    <label for="internal_notes">Notas Internas (No visibles para el cliente)</label>
                    <textarea name="internal_notes" id="internal_notes" class="form-control @error('internal_notes') is-invalid @enderror" rows="2">{{ old('internal_notes') }}</textarea>
                    @error('internal_notes') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                </div>

            </div> {{-- /.card-body --}}
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cotización</button>
                <a href="{{ route('quotes.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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
        // console.log('Formulario de creación de cotizaciones v5.');

        let IVA_RATE = parseFloat($('#taxPercentageInput').val()) || {{ $iva_rate ?? 16.00 }};
        let BCV_RATE = parseFloat("{{ $bcv_rate ?? 0 }}");
        let PROMEDIO_RATE = parseFloat("{{ $promedio_rate ?? 0 }}");
        // $('#ivaRateSpan').text(IVA_RATE.toFixed(2)); // taxPercentageInput es ahora un input

        // let itemIndex = $('#quoteItemsTbody tr').length > 0 ? $('#quoteItemsTbody tr').length : 0; // Se recalculará en addNewItemRow


        function formatCurrency(value) {
            value = parseFloat(value);
            if (isNaN(value)) value = 0;
            return value.toFixed(2);
        }

        function addNewItemRow() {
            let template = $('#quoteItemTemplate').html();
            // Usa el número de filas actual para asegurar unicidad de índice, incluso si se borran filas intermedias
            let newRowIndex = $('#quoteItemsTbody tr').length ? (parseInt($('#quoteItemsTbody tr:last').find('input[name^="items["]').attr('name').match(/items\[(\d+)\]/)[1]) + 1) : 0;
            template = template.replace(/__INDEX__/g, newRowIndex);
            let $newRow = $(template);

            $newRow.find('.item-name').val('');
            $newRow.find('.item-cost').val('0.00').prop('readonly', false);
            $newRow.find('.item-price').val('0.00').prop('readonly', false);
            $newRow.find('.item-calc-method').prop('checked', false);
            $newRow.find('.item-calc-method-hidden').val('');
            $newRow.find('.item-applied-rate-hidden').val('');


            $('#quoteItemsTbody').append($newRow);
             $newRow.find('input[type="checkbox"]').each(function() {
                let oldId = $(this).attr('id');
                let newId = oldId.replace('__INDEX__', newRowIndex); // Usar el newRowIndex calculado
                $(this).attr('id', newId);
                $(this).next('label').attr('for', newId);
            });

            attachItemEventListenersToRow($newRow);
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

        function attachItemEventListenersToRow($row) {
            $row.find('.item-quantity, .item-cost, .item-price').off('.quoteitem').on('input.quoteitem change.quoteitem', function() {
                let $currentRow = $(this).closest('tr');
                let isPriceField = $(this).hasClass('item-price');
                let isPriceReadonly = $currentRow.find('.item-price').prop('readonly');

                if (isPriceField && isPriceReadonly) { /* No hacer nada */ }
                else {
                    let quantity = parseFloat($currentRow.find('.item-quantity').val()) || 0;
                    let price = parseFloat($currentRow.find('.item-price').val()) || 0;
                    $currentRow.find('.item-line-total').text(formatCurrency(quantity * price));
                }
                calculateAll();
            });

            $row.find('.item-calc-method').off('.quoteitem').on('change.quoteitem', function() {
                let $currentRow = $(this).closest('tr');
                let $checkboxChanged = $(this);
                let methodName = $checkboxChanged.val();
                let cost = parseFloat($currentRow.find('.item-cost').val()) || 0;
                let $itemPriceInput = $currentRow.find('.item-price');
                let $hiddenMethodInput = $currentRow.find('.item-calc-method-hidden');
                let $hiddenAppliedRateInput = $currentRow.find('.item-applied-rate-hidden');

                $currentRow.find('.item-calc-method').not($checkboxChanged).prop('checked', false);

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
                        $hiddenAppliedRateInput.val('');
                        $itemPriceInput.prop('readonly', false);
                        return;
                    }
                    $itemPriceInput.val( (cost * rate).toFixed(2) );
                    $hiddenAppliedRateInput.val(rate.toFixed(4));
                    $itemPriceInput.prop('readonly', true);
                } else {
                    $hiddenMethodInput.val('');
                    $hiddenAppliedRateInput.val('');
                    $itemPriceInput.prop('readonly', false);
                }
                $itemPriceInput.trigger('change.quoteitem');
            });
        }
        
        // Aplicar listeners a filas existentes al cargar la página (para old data)
        $('#quoteItemsTbody tr').each(function() {
            attachItemEventListenersToRow($(this));
        });


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

            let discountValue = parseFloat($('#discount_value').val()) || 0;
            let discountType = $('#discount_type').val();
            let discountAmount = (discountType === 'percentage' && discountValue > 0) ? (subtotal * discountValue) / 100 : discountValue;
            discountAmount = Math.min(discountAmount, subtotal);

            $('#discountAmountText').text(formatCurrency(discountAmount));

            let taxableBase = subtotal - discountAmount;
            $('#taxableBaseText').text(formatCurrency(taxableBase));

            let current_iva_rate = parseFloat($('#taxPercentageInput').val()) || 0;
            let taxAmount = (taxableBase * current_iva_rate) / 100;
            $('#taxAmountText').text(formatCurrency(taxAmount));

            let total = taxableBase + taxAmount;
            $('#quoteTotalText').text(formatCurrency(total));
        }

        $('#discount_value, #discount_type, #taxPercentageInput').on('input change', calculateAll);
        
        // Llamada inicial para cálculos
        calculateAll();

        if ($('#quoteItemsTbody tr').length === 0 && !@json(old('items'))) {
            $('#addManualItemButton').trigger('click');
        }
    });
    </script>
@stop