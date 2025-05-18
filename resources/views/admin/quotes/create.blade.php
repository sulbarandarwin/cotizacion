@extends('adminlte::page')

@section('title', 'Crear Nueva Cotización')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Crear Nueva Cotización</h1>
        <span class="text-muted">N° Cotización (aprox.): {{ $nextQuoteNumber ?? 'COT-XXXX' }}</span>
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

    <form id="createQuoteForm" action="{{ route('quotes.store') }}" method="POST">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Información General y Cliente</h3>
                 <div class="card-tools">
                    <button type="button" id="autoSaveTriggerButtonCreate" class="btn btn-sm btn-outline-secondary" title="Autoguardado no disponible para nuevas cotizaciones hasta el primer guardado.">
                        <i class="fas fa-save"></i> Autoguardar
                    </button>
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
                                    <option value="{{ $id }}" {{ old('client_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="issue_date">Fecha Emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', now()->format('Y-m-d')) }}" required>
                            @error('issue_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="expiry_date">Fecha Validez <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', now()->addDays($default_validity_days ?? 15)->format('Y-m-d')) }}" required>
                            @error('expiry_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="base_currency">Moneda <span class="text-danger">*</span></label>
                            <select name="base_currency" id="base_currency" class="form-control @error('base_currency') is-invalid @enderror" required>
                                <option value="USD" {{ old('base_currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="BS" {{ old('base_currency', 'USD') == 'BS' ? 'selected' : '' }}>BS</option>
                            </select>
                            @error('base_currency') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_bcv">Tasa BCV (Opcional)</label>
                            <input type="number" step="any" name="exchange_rate_bcv" id="exchange_rate_bcv" class="form-control @error('exchange_rate_bcv') is-invalid @enderror rate-input" value="{{ old('exchange_rate_bcv', number_format($bcv_rate ?? 0, 2, '.', '')) }}" placeholder="Tasa BCV del día">
                            @error('exchange_rate_bcv') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exchange_rate_promedio">Tasa Promedio (Opcional)</label>
                            <input type="number" step="any" name="exchange_rate_promedio" id="exchange_rate_promedio" class="form-control @error('exchange_rate_promedio') is-invalid @enderror rate-input" value="{{ old('exchange_rate_promedio', number_format($promedio_rate ?? 0, 2, '.', '')) }}" placeholder="Tasa promedio del día">
                            @error('exchange_rate_promedio') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="profit_percentage">Porcentaje Utilidad (%)</label>
                            <input type="number" step="0.01" name="profit_percentage" id="profit_percentage" class="form-control @error('profit_percentage') is-invalid @enderror calc-trigger" value="{{ old('profit_percentage', number_format($default_profit_percentage ?? 0, 2, '.', '')) }}" placeholder="Ej: 20.00">
                            @error('profit_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Ítems de la Cotización <span class="text-danger">*</span></h3>
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
                            @if(is_array(old('items')))
                                @foreach(old('items') as $index => $item_data)
                                    @php $item = (object) $item_data; @endphp
                                    <tr class="quote-item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id ?? '' }}" class="item-product-id">
                                            <input type="text" name="items[{{ $index }}][manual_product_name]" class="form-control form-control-sm item-name @error('items.'.$index.'.manual_product_name') is-invalid @enderror" value="{{ $item->manual_product_name ?? '' }}" placeholder="Nombre del Producto" required>
                                        </td>
                                        <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-quantity calc-trigger @error('items.'.$index.'.quantity') is-invalid @enderror" value="{{ $item->quantity ?? 1 }}" step="any" min="0.01" required></td>
                                        <td><input type="text" name="items[{{ $index }}][manual_product_unit]" class="form-control form-control-sm item-unit" value="{{ $item->manual_product_unit ?? 'Medida' }}" placeholder="Ej: Pza, Kg"></td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][cost]" class="form-control form-control-sm item-cost calc-trigger @error('items.'.$index.'.cost') is-invalid @enderror" value="{{ number_format((float)($item->cost ?? 0), 2, '.', '') }}" step="any" min="0" required>
                                        </td>
                                        <td>
                                            <div class="form-check text-center">
                                                <input type="checkbox" name="items[{{ $index }}][cost_plus_iva]" value="1" class="form-check-input item-cost-plus-iva calc-trigger" style="margin-top: 0.3rem; margin-left: -0.5rem;" {{ !empty($item->cost_plus_iva) && $item->cost_plus_iva == '1' ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][price_calculation_method]" class="item-price-calc-method" value="{{ $item->price_calculation_method ?? 'manual' }}">
                                            <input type="hidden" name="items[{{ $index }}][applied_rate_value]" class="item-applied-rate" value="{{ $item->applied_rate_value ?? '' }}">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-xs btn-outline-info calc-price-btn {{ ($item->price_calculation_method ?? 'manual') == 'promedio' ? 'active' : '' }}" data-method="promedio" title="Usar Tasa Promedio">
                                                    <input type="radio" name="items[{{ $index }}][calc_option]" value="promedio" class="calc-trigger" {{ ($item->price_calculation_method ?? 'manual') == 'promedio' ? 'checked' : '' }}> <span class="d-none d-sm-inline">Prom.</span>
                                                </label>
                                                <label class="btn btn-xs btn-outline-warning calc-price-btn {{ ($item->price_calculation_method ?? 'manual') == 'bcv' ? 'active' : '' }}" data-method="bcv" title="Usar Tasa BCV">
                                                    <input type="radio" name="items[{{ $index }}][calc_option]" value="bcv" class="calc-trigger" {{ ($item->price_calculation_method ?? 'manual') == 'bcv' ? 'checked' : '' }}> <span class="d-none d-sm-inline">BCV</span>
                                                </label>
                                                <label class="btn btn-xs btn-outline-secondary calc-price-btn {{ ($item->price_calculation_method ?? 'manual') == 'manual' ? 'active' : '' }}" data-method="manual" title="Precio Manual">
                                                    <input type="radio" name="items[{{ $index }}][calc_option]" value="manual" class="calc-trigger" {{ ($item->price_calculation_method ?? 'manual') == 'manual' ? 'checked' : '' }}> <i class="fas fa-edit"></i>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input type="number" name="items[{{ $index }}][price]" class="form-control form-control-sm item-price calc-trigger @error('items.'.$index.'.price') is-invalid @enderror" value="{{ number_format((float)($item->price ?? 0), 2, '.', '') }}" step="any" min="0" required {{ ($item->price_calculation_method ?? 'manual') != 'manual' ? 'readonly' : '' }}></td>
                                        <td class="text-right"><span class="item-line-total">0.00</span></td>
                                        <td><button type="button" class="btn btn-xs btn-danger removeItemButton"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
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
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control" rows="3">{{ old('terms_and_conditions', $default_terms_conditions ?? '') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes_to_client">Notas para el Cliente (Opcional)</label>
                            <textarea name="notes_to_client" id="notes_to_client" class="form-control" rows="2">{{ old('notes_to_client') }}</textarea>
                        </div>
                         <div class="form-group">
                            <label for="internal_notes">Notas Internas (No visible al cliente, Opcional)</label>
                            <textarea name="internal_notes" id="internal_notes" class="form-control" rows="2">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Resumen y Descuentos</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th>Subtotal Bruto:</th><td class="text-right" id="quoteSubtotalText">0.00</td></tr>
                            <tr>
                                <td>
                                    <label for="discount_type">Descuento Global:</label>
                                    <select name="discount_type" id="discount_type" class="form-control form-control-sm d-inline-block calc-trigger" style="width: auto;">
                                        <option value="">Ninguno</option>
                                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Monto Fijo</option>
                                        <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <input type="number" step="any" name="discount_value" id="discount_value" class="form-control form-control-sm d-inline-block calc-trigger @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', 0) }}" style="width: 100px;">
                                    @error('discount_value') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr><th>Monto Descuento:</th><td class="text-right" id="discountAmountText">0.00</td></tr>
                            <tr><th>Base Imponible:</th><td class="text-right" id="taxableBaseText">0.00</td></tr>
                            <tr>
                                <td>
                                    <label for="tax_percentage">Impuesto (%):</label>
                                    <input type="number" step="any" name="tax_percentage" id="taxPercentageInput" class="form-control form-control-sm d-inline-block calc-trigger @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $iva_rate ?? 16.00) }}" required style="width: 80px;">
                                    @error('tax_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </td>
                                <td class="text-right" id="taxAmountText">0.00</td>
                            </tr>
                            <tr class="bg-light"><th class="h4">Total Cotización:</th><td class="text-right h4" id="quoteTotalText">0.00</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cotización</button>
            <a href="{{ route('quotes.index') }}" class="btn btn-secondary">Cancelar</a>
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
    console.log("Create Quote JS: Documento listo para Edición 6 Final.");

    if ($.fn.select2) {
        $('#client_id').select2({ placeholder: "Seleccione un cliente", allowClear: true });
    } else {
        console.error("Create Quote JS: Select2 no está cargado.");
    }

    let globalItemIndex = 0;
    @if(is_array(old('items')))
        globalItemIndex = {{ count(old('items')) }};
        console.log("Create Quote JS: Hay datos 'old'. globalItemIndex: " + globalItemIndex);
        @foreach(old('items') as $index => $item_data)
            @php $item = (object) $item_data; @endphp
            let oldCostPlusIva_{{ $index }} = "{{ old('items.'.$index.'.cost_plus_iva') }}";
            if (oldCostPlusIva_{{ $index }} === '1') {
                $('input[name="items[{{ $index }}][cost_plus_iva]"]').prop('checked', true);
            }
            let oldCalcMethod_{{ $index }} = "{{ old('items.'.$index.'.price_calculation_method', 'manual') }}";
            let radioToCheck_{{ $index }} = $(`input[name="items[{{ $index }}][calc_option]"][value="${oldCalcMethod_{{ $index }}}"]`);
            if (radioToCheck_{{ $index }}.length) {
                radioToCheck_{{ $index }}.prop('checked', true);
                radioToCheck_{{ $index }}.closest('.btn-group').find('.btn').removeClass('active');
                radioToCheck_{{ $index }}.closest('label.btn').addClass('active');
                if (oldCalcMethod_{{ $index }} !== 'manual') {
                    $('input[name="items[{{ $index }}][price]"]').prop('readonly', true);
                } else {
                    $('input[name="items[{{ $index }}][price]"]').prop('readonly', false);
                }
            }
        @endforeach
    @endif

    function addQuoteItemRow(item = {}, currentIndex) {
        console.log(`Create Quote JS: addQuoteItemRow para índice ${currentIndex}`, item);
        let costValue = item.cost ? parseFloat(item.cost).toFixed(2) : '0.00';
        let priceValue = item.price ? parseFloat(item.price).toFixed(2) : costValue;
        let quantityValue = item.quantity || 1;
        let productName = item.manual_product_name || item.name || '';
        let productUnit = item.manual_product_unit || item.unit_of_measure || 'Medida';
        let productId = item.product_id || '';
        let costPlusIvaChecked = item.cost_plus_iva ? 'checked' : '';

        const newRowHtml = `
            <tr class="quote-item-row">
                <td>
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
                    <input type="hidden" name="items[${currentIndex}][price_calculation_method]" class="item-price-calc-method" value="manual">
                    <input type="hidden" name="items[${currentIndex}][applied_rate_value]" class="item-applied-rate" value="">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-xs btn-outline-info calc-price-btn" data-method="promedio" title="Usar Tasa Promedio">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="promedio" class="calc-trigger"> <span class="d-none d-sm-inline">Prom.</span>
                        </label>
                        <label class="btn btn-xs btn-outline-warning calc-price-btn" data-method="bcv" title="Usar Tasa BCV">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="bcv" class="calc-trigger"> <span class="d-none d-sm-inline">BCV</span>
                        </label>
                        <label class="btn btn-xs btn-outline-secondary calc-price-btn active" data-method="manual" title="Precio Manual">
                            <input type="radio" name="items[${currentIndex}][calc_option]" value="manual" class="calc-trigger" checked> <i class="fas fa-edit"></i>
                        </label>
                    </div>
                </td>
                <td><input type="number" name="items[${currentIndex}][price]" class="form-control form-control-sm item-price calc-trigger" value="${priceValue}" step="any" min="0" required></td>
                <td class="text-right"><span class="item-line-total">0.00</span></td>
                <td><button type="button" class="btn btn-xs btn-danger removeItemButton"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#quoteItemsTbody').append(newRowHtml);
        $('[data-toggle="tooltip"]').tooltip();
        calculateAll();
    }

    $('#addManualItemButton').off('click').on('click', function() { addQuoteItemRow({}, globalItemIndex); globalItemIndex++; });
    $('#searchProductButton').off('click').on('click', function() { if ($.fn.modal) $('#searchProductModal').modal('show'); });
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
    $(document).on('click', '.removeItemButton', function() { $(this).closest('tr').remove(); calculateAll(); });
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
    
    // FUNCIÓN calculateAll() CON LÓGICA COMPLETA SEGÚN EJEMPLOS
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
                // En modo manual, el precio base para la utilidad es el costoAjustadoPorIVA,
                // pero si el usuario ya escribió algo en precio, se usa eso.
                let manualPrice = parseFloat(priceInput.val().replace(',', '.')) || 0;
                precioIntermedio = manualPrice > 0 ? manualPrice : costoAjustadoPorIVA;
            } else {
                priceInput.prop('readonly', true);
                if (calcMethod === 'promedio') {
                    if (globalPromedioRate > 0 && globalBcvRate > 0) {
                        precioIntermedio = (costoAjustadoPorIVA * globalPromedioRate) / globalBcvRate;
                        row.find('.item-applied-rate').val((globalPromedioRate / globalBcvRate).toFixed(4));
                    } else {
                        precioIntermedio = costoAjustadoPorIVA; 
                        row.find('.calc-price-btn[data-method="manual"]').addClass('active').siblings().removeClass('active').find('input').prop('checked',true);
                        priceInput.prop('readonly', false); // Permitir edición manual si falla
                        row.find('.item-price-calc-method').val('manual');
                         console.warn(`Ítem ${rowIndex + 1}: Cálculo Promedio no posible, tasas no válidas. Cambiado a manual.`);
                    }
                } else if (calcMethod === 'bcv') {
                    // Para BCV, el precio intermedio es simplemente el costoAjustadoPorIVA (según tu lógica de ejemplo 2 y 4)
                    precioIntermedio = costoAjustadoPorIVA; 
                    row.find('.item-applied-rate').val(globalBcvRate > 0 ? globalBcvRate.toFixed(4) : ''); // Guardar la tasa BCV solo como referencia
                } else { 
                     precioIntermedio = costoAjustadoPorIVA;
                     priceInput.prop('readonly', false);
                     row.find('.calc-price-btn[data-method="manual"]').addClass('active').siblings().removeClass('active').find('input').prop('checked',true);
                     row.find('.item-price-calc-method').val('manual');
                }
            }

            // Aplicar Porcentaje de Utilidad Global
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
    
    if ($('#quoteItemsTbody tr.quote-item-row').length > 0) {
        calculateAll();
    }

    $('#autoSaveTriggerButtonCreate').on('click', function() {
        alert("Autoguardado para nuevas cotizaciones: Para que funcione, primero guarde la cotización como borrador.");
    });
});
</script>
@endpush
