@extends('adminlte::page')

@section('title', 'Configuración del Sistema')

@section('content_header')
    <h1 class="m-0 text-dark">Configuración del Sistema</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-ban"></i> {{ session('error') }}
                </div>
            @endif
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

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card card-primary card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="company-data-tab" data-toggle="pill" href="#company-data" role="tab" aria-controls="company-data" aria-selected="true">Datos de la Empresa</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="quote-settings-tab" data-toggle="pill" href="#quote-settings" role="tab" aria-controls="quote-settings" aria-selected="false">Cotizaciones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="financial-settings-tab" data-toggle="pill" href="#financial-settings" role="tab" aria-controls="financial-settings" aria-selected="false">Financiero/Impuestos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="payment-settings-tab" data-toggle="pill" href="#payment-settings" role="tab" aria-controls="payment-settings" aria-selected="false">Pagos</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="settingsTabsContent">
                            {{-- Pestaña Datos de la Empresa --}}
                            <div class="tab-pane fade show active" id="company-data" role="tabpanel" aria-labelledby="company-data-tab">
                                <div class="form-group">
                                    <label for="company_name">Nombre de la Empresa</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $settingsArray['company_name'] ?? '') }}">
                                    @error('company_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="company_rif">RIF/Identificación Fiscal</label>
                                    <input type="text" name="company_rif" id="company_rif" class="form-control @error('company_rif') is-invalid @enderror" value="{{ old('company_rif', $settingsArray['company_rif'] ?? '') }}">
                                    @error('company_rif') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="company_address">Dirección Fiscal</label>
                                    <textarea name="company_address" id="company_address" class="form-control @error('company_address') is-invalid @enderror" rows="3">{{ old('company_address', $settingsArray['company_address'] ?? '') }}</textarea>
                                    @error('company_address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Teléfono</label>
                                            <input type="text" name="company_phone" id="company_phone" class="form-control @error('company_phone') is-invalid @enderror" value="{{ old('company_phone', $settingsArray['company_phone'] ?? '') }}">
                                            @error('company_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email de Contacto</label>
                                            <input type="email" name="company_email" id="company_email" class="form-control @error('company_email') is-invalid @enderror" value="{{ old('company_email', $settingsArray['company_email'] ?? '') }}">
                                            @error('company_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_nit">NIT (Si aplica)</label>
                                            <input type="text" name="company_nit" id="company_nit" class="form-control @error('company_nit') is-invalid @enderror" value="{{ old('company_nit', $settingsArray['company_nit'] ?? '') }}">
                                            @error('company_nit') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_fax">Fax (Si aplica)</label>
                                            <input type="text" name="company_fax" id="company_fax" class="form-control @error('company_fax') is-invalid @enderror" value="{{ old('company_fax', $settingsArray['company_fax'] ?? '') }}">
                                            @error('company_fax') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="company_logo_file">Logo de la Empresa</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="company_logo_file" class="custom-file-input @error('company_logo_file') is-invalid @enderror" id="company_logo_file">
                                            <label class="custom-file-label" for="company_logo_file">Elegir archivo...</label>
                                        </div>
                                    </div>
                                    @error('company_logo_file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    @if(!empty($settingsArray['company_logo']))
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($settingsArray['company_logo']) }}" alt="Logo Actual" style="max-height: 100px; border: 1px solid #ccc; padding: 5px;">
                                            <p class="text-muted_"><small>Logo actual. Subir uno nuevo lo reemplazará.</small></p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Pestaña Configuraciones de Cotización --}}
                            <div class="tab-pane fade" id="quote-settings" role="tabpanel" aria-labelledby="quote-settings-tab">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="default_validity_days">Validez por Defecto de Oferta (días)</label>
                                            <input type="number" name="default_validity_days" id="default_validity_days" class="form-control @error('default_validity_days') is-invalid @enderror" value="{{ old('default_validity_days', $settingsArray['default_validity_days'] ?? '15') }}" min="1">
                                            @error('default_validity_days') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="default_profit_percentage">Porcentaje de Utilidad por Defecto (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="default_profit_percentage" id="default_profit_percentage" class="form-control @error('default_profit_percentage') is-invalid @enderror" value="{{ old('default_profit_percentage', $settingsArray['default_profit_percentage'] ?? '20.00') }}" step="0.01" min="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('default_profit_percentage') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="default_terms_conditions">Términos y Condiciones por Defecto</label>
                                    <textarea name="default_terms_conditions" id="default_terms_conditions" class="form-control @error('default_terms_conditions') is-invalid @enderror" rows="5">{{ old('default_terms_conditions', $settingsArray['default_terms_conditions'] ?? '') }}</textarea>
                                    @error('default_terms_conditions') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="default_payment_condition">Condiciones de Pago por Defecto</label>
                                    <input type="text" name="default_payment_condition" id="default_payment_condition" class="form-control @error('default_payment_condition') is-invalid @enderror" value="{{ old('default_payment_condition', $settingsArray['default_payment_condition'] ?? 'Contado') }}">
                                    @error('default_payment_condition') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                     <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quote_prefix">Prefijo Número de Cotización</label>
                                            <input type="text" name="quote_prefix" id="quote_prefix" class="form-control @error('quote_prefix') is-invalid @enderror" value="{{ old('quote_prefix', $settingsArray['quote_prefix'] ?? 'COT-') }}">
                                            @error('quote_prefix') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    {{-- El 'next_quote_number' es mejor manejarlo internamente por el QuoteService para evitar conflictos
                                         pero se puede mostrar el valor actual si se desea.
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="next_quote_number">Próximo Número de Cotización (informativo)</label>
                                            <input type="number" name="next_quote_number" id="next_quote_number" class="form-control" value="{{ old('next_quote_number', $settingsArray['next_quote_number'] ?? '1') }}" readonly>
                                        </div>
                                    </div>
                                    --}}
                                </div>
                            </div>

                            {{-- Pestaña Financiero/Impuestos --}}
                            <div class="tab-pane fade" id="financial-settings" role="tabpanel" aria-labelledby="financial-settings-tab">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="iva_rate">Tasa de IVA (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="iva_rate" id="iva_rate" class="form-control @error('iva_rate') is-invalid @enderror" value="{{ old('iva_rate', $settingsArray['iva_rate'] ?? '16.00') }}" step="0.01" min="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('iva_rate') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tax_label">Etiqueta del Impuesto (Ej: IVA)</label>
                                            <input type="text" name="tax_label" id="tax_label" class="form-control @error('tax_label') is-invalid @enderror" value="{{ old('tax_label', $settingsArray['tax_label'] ?? 'IVA') }}">
                                            @error('tax_label') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h5 class="mb-3">Tasas de Cambio (Referenciales)</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="bcv_rate">Tasa BCV</label>
                                            <input type="number" name="bcv_rate" id="bcv_rate" class="form-control @error('bcv_rate') is-invalid @enderror" value="{{ old('bcv_rate', $settingsArray['bcv_rate'] ?? '0.00') }}" step="0.0001" min="0">
                                            @error('bcv_rate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="promedio_rate">Tasa Promedio</label>
                                            <input type="number" name="promedio_rate" id="promedio_rate" class="form-control @error('promedio_rate') is-invalid @enderror" value="{{ old('promedio_rate', $settingsArray['promedio_rate'] ?? '0.00') }}" step="0.0001" min="0">
                                            @error('promedio_rate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h5 class="mb-3">Símbolos de Moneda</h5>
                                 <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="base_currency_symbol">Símbolo Moneda Base (Ej: $)</label>
                                            <input type="text" name="base_currency_symbol" id="base_currency_symbol" class="form-control @error('base_currency_symbol') is-invalid @enderror" value="{{ old('base_currency_symbol', $settingsArray['base_currency_symbol'] ?? '$') }}">
                                            @error('base_currency_symbol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="secondary_currency_symbol">Símbolo Moneda Secundaria (Ej: Bs.)</label>
                                            <input type="text" name="secondary_currency_symbol" id="secondary_currency_symbol" class="form-control @error('secondary_currency_symbol') is-invalid @enderror" value="{{ old('secondary_currency_symbol', $settingsArray['secondary_currency_symbol'] ?? 'Bs.') }}">
                                            @error('secondary_currency_symbol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Pestaña Información de Pagos --}}
                            <div class="tab-pane fade" id="payment-settings" role="tabpanel" aria-labelledby="payment-settings-tab">
                                <div class="form-group">
                                    <label for="payment_bank_details">Detalles Bancarios (Para mostrar en cotización)</label>
                                    <textarea name="payment_bank_details" id="payment_bank_details" class="form-control @error('payment_bank_details') is-invalid @enderror" rows="5">{{ old('payment_bank_details', $settingsArray['payment_bank_details'] ?? '') }}</textarea>
                                    @error('payment_bank_details') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="payment_other_methods">Otros Métodos de Pago (Para mostrar en cotización)</label>
                                    <textarea name="payment_other_methods" id="payment_other_methods" class="form-control @error('payment_other_methods') is-invalid @enderror" rows="5">{{ old('payment_other_methods', $settingsArray['payment_other_methods'] ?? '') }}</textarea>
                                    @error('payment_other_methods') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Configuraciones
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Script para mostrar el nombre del archivo en el input de tipo file (Bootstrap 4)
        $('.custom-file-input').on('change', function(event) {
            var inputFile = event.target;
            var fileName = inputFile.files[0].name;
            $(inputFile).next('.custom-file-label').html(fileName);
        });

        // Activar la pestaña que tuvo un error de validación si existe
        @if($errors->any())
            var firstErrorField = "{{ $errors->keys()[0] }}";
            // Intentar encontrar el tab basado en el campo con error
            var $fieldWithError = $('#' + firstErrorField);
            if($fieldWithError.length){
                var $tabPane = $fieldWithError.closest('.tab-pane');
                if($tabPane.length){
                    var tabId = $tabPane.attr('id');
                    $('#settingsTabs a[href="#'+tabId+'"]').tab('show');
                }
            }
        @endif
    });
</script>
@stop