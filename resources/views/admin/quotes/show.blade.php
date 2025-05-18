@extends('adminlte::page')

@section('title', 'Detalle de Cotización N° ' . $quote->quote_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalle de Cotización: <strong>{{ $quote->quote_number }}</strong></h1>
        <a href="{{ route('quotes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        {{-- ... otros mensajes de sesión ... --}}

        {{-- Acciones Principales --}}
        <div class="row mb-3 no-print">
            <div class="col-md-12 text-right">
                 @if(Auth::user()->can('editar cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
                    <a href="{{ route('quotes.edit', $quote->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-edit"></i> Editar Cotización
                    </a>
                @endif
                <button onclick="window.print();" class="btn btn-sm btn-default"><i class="fas fa-print"></i> Imprimir</button>
                <a href="{{ route('quotes.downloadPdf', $quote->id) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </a>
                 @if(Auth::user()->can('duplicar cotizaciones') || Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Vendedor'))
                <form action="{{ route('quotes.duplicate', $quote->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de duplicar esta cotización? Se creará una nueva cotización basada en esta.');">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-copy"></i> Duplicar Cotización
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Panel para cambiar estado --}}
        {{-- ... (código del panel de cambio de estado sin cambios) ... --}}

        <div class="invoice p-3 mb-3">
            {{-- ... (código de información de la empresa, cliente, detalles de la cotización) ... --}}
            <div class="row">
                <div class="col-12">
                    <h4>
                        @php 
                            $logoPath = $companySettings['company_logo'] ?? null;
                            $logoUrl = $logoPath ? asset('storage/' . $logoPath) : asset('vendor/adminlte/dist/img/AdminLTELogo.png');
                            if ($logoPath && !Str::startsWith($logoPath, 'http') && !file_exists(public_path('storage/' . $logoPath))) {
                                // $logoUrl = asset('vendor/adminlte/dist/img/AdminLTELogo.png');
                            }
                        @endphp
                        <img src="{{ $companySettings['company_logo_base64'] ?? $logoUrl }}" alt="Logo Empresa" style="width: 50px; height: auto; margin-right: 10px;">
                        {{ $companySettings['company_name'] ?? 'Nombre de tu Empresa' }}
                        <small class="float-right">Fecha: {{ now()->format('d/m/Y') }}</small>
                    </h4>
                </div>
            </div>
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    De:
                    <address>
                        <strong>{{ $companySettings['company_name'] ?? 'Nombre de tu Empresa' }}</strong><br>
                        RIF: {{ $companySettings['company_rif'] ?? 'J-00000000-0' }}<br>
                        {{ $companySettings['company_address'] ?? 'Dirección de tu Empresa' }}<br>
                        Teléfono: {{ $companySettings['company_phone'] ?? '(xxx) xxxx-xxxx' }}<br>
                        Email: {{ $companySettings['company_email'] ?? 'email@empresa.com' }}
                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    Para:
                    <address>
                        <strong>{{ $quote->client->name ?? 'N/A' }}</strong><br>
                        RIF/Cédula: {{ $quote->client->identifier ?? 'N/A' }}<br>
                        {{ $quote->client->address ?? 'N/A' }}<br>
                        Teléfono: {{ $quote->client->phone ?? 'N/A' }}<br>
                        Email: {{ $quote->client->email ?? 'N/A' }}
                    </address>
                </div>
                <div class="col-sm-4 invoice-col">
                    <b>Cotización #{{ $quote->quote_number }}</b><br>
                    <br>
                    <b>Fecha Emisión:</b> {{ $quote->issue_date ? $quote->issue_date->format('d/m/Y') : 'N/A' }}<br>
                    <b>Fecha Vencimiento:</b> {{ $quote->expiry_date ? $quote->expiry_date->format('d/m/Y') : 'N/A' }}<br>
                    <b>Vendedor:</b> {{ $quote->user->name ?? 'N/A' }}<br>
                    <b>Estado:</b> <span class="badge badge-{{ $quote->status_class }} p-1">{{ $quote->status_text }}</span>
                </div>
            </div>


            {{-- Tabla de Ítems --}}
            <div class="row mt-4">
                <div class="col-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cant.</th>
                                <th>Producto/Servicio</th>
                                <th>Medida</th>
                                <th class="text-right">Costo Unit. ({{ $quote->base_currency }})</th>
                                <th class="text-right">Precio Unit. ({{ $quote->base_currency }})</th>
                                <th class="text-right">Subtotal ({{ $quote->base_currency }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                            <tr>
                                <td>{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td>
                                    {{ $item->product->name ?? $item->manual_product_name }}
                                    @if($item->product_id && $item->product) <small class="text-muted"> (Cód: {{ $item->product->code }})</small> @endif
                                    @if($item->estimated_delivery_time) <br><small><em>Entrega: {{ $item->estimated_delivery_time }}</em></small> @endif
                                </td>
                                <td>{{ ($item->product->unit_of_measure ?? $item->manual_product_unit) ?? 'N/A' }}</td>
                                <td class="text-right">{{ number_format($item->cost, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($item->price, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($item->line_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sección de Totales, Notas y Términos --}}
            <div class="row mt-4">
                <div class="col-md-6">
                    @if($quote->notes_to_client)
                    <p class="lead">Notas para el Cliente:</p>
                    <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                        {!! nl2br(e($quote->notes_to_client)) !!}
                    </p>
                    @endif

                    @if($quote->terms_and_conditions)
                    <p class="lead" style="margin-top: {{ $quote->notes_to_client ? '15px' : '0' }};">Términos y Condiciones:</p>
                    <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                       {!! nl2br(e($quote->terms_and_conditions)) !!}
                    </p>
                    @endif

                    {{-- INICIO: NUEVA SECCIÓN DE MÉTODOS DE PAGO --}}
                    @if(!empty($companySettings['payment_bank_details']) || !empty($companySettings['payment_other_methods']))
                    <div style="margin-top: 15px; padding-top:10px; border-top: 1px solid #eee;">
                        <p class="lead">Información de Pago:</p>
                        @if(!empty($companySettings['payment_bank_details']))
                            <strong>Cuentas Bancarias:</strong>
                            <div class="text-muted well well-sm shadow-none" style="margin-top: 5px; margin-bottom:10px; white-space: pre-wrap; font-size:0.9em;">{!! nl2br(e($companySettings['payment_bank_details'])) !!}</div>
                        @endif
                        @if(!empty($companySettings['payment_other_methods']))
                            <strong>Otros Métodos de Pago:</strong>
                            <div class="text-muted well well-sm shadow-none" style="margin-top: 5px; white-space: pre-wrap; font-size:0.9em;">{!! nl2br(e($companySettings['payment_other_methods'])) !!}</div>
                        @endif
                    </div>
                    @endif
                    {{-- FIN: NUEVA SECCIÓN DE MÉTODOS DE PAGO --}}

                </div>
                <div class="col-md-6">
                    {{-- ... (código de resumen de montos sin cambios) ... --}}
                    <p class="lead">Resumen de Montos ({{ $quote->base_currency }})</p>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width:50%">Subtotal Bruto:</th>
                                <td class="text-right">{{ number_format($quote->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @if($quote->discount_amount > 0)
                            <tr>
                                <th>
                                    Descuento 
                                    @if($quote->discount_type == 'percentage')
                                        ({{ rtrim(rtrim(number_format($quote->discount_value, 2, ',', '.'), '0'), ',') }}%)
                                    @elseif($quote->discount_type == 'fixed')
                                        (Monto Fijo)
                                    @endif
                                    :
                                </th>
                                <td class="text-right">- {{ number_format($quote->discount_amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Subtotal Neto:</th>
                                <td class="text-right">{{ number_format($quote->subtotal - $quote->discount_amount, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Impuesto ({{ rtrim(rtrim(number_format($quote->tax_percentage, 2, ',', '.'),'0'),',') }}%):</th>
                                <td class="text-right">{{ number_format($quote->tax_amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="h4">Total {{ $quote->base_currency }}:</th>
                                <td class="text-right"><strong>{{ number_format($quote->total, 2, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                     @if($quote->base_currency === 'USD' && ($quote->exchange_rate_bcv ?? $quote->exchange_rate_promedio))
                        @php
                            $rateToUse = $quote->exchange_rate_bcv ?? $quote->exchange_rate_promedio;
                            $rateLabel = $quote->exchange_rate_bcv ? 'BCV' : 'Promedio';
                        @endphp
                        <p class="text-muted" style="font-size: 0.9em;">
                            Total en BS (Referencial {{ $rateLabel }}: {{ number_format($rateToUse, 2, ',', '.') }}): 
                            <strong>{{ number_format($quote->total * $rateToUse, 2, ',', '.') }} BS</strong>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Historial de Cambios --}}
            {{-- ... (código del historial sin cambios) ... --}}
            @if($quote->history && $quote->history->count() > 0)
            <div class="row mt-4 no-print">
                <div class="col-12">
                    <h5>Historial de Cambios</h5>
                    <ul class="list-group list-group-flush">
                        @foreach($quote->history as $hist)
                            <li class="list-group-item text-sm">
                                <i class="fas fa-history mr-1"></i>
                                <strong>{{ $hist->created_at->format('d/m/Y H:i') }}</strong> - 
                                {{ $hist->user->name ?? 'Sistema' }}: {{ $hist->action }}
                                @if($hist->details && is_array($hist->details))
                                    @if(isset($hist->details['status_anterior']) && isset($hist->details['status_nuevo']))
                                        (De: {{ App\Models\Quote::getStatusMap()[$hist->details['status_anterior']] ?? $hist->details['status_anterior'] }} 
                                        a: {{ App\Models\Quote::getStatusMap()[$hist->details['status_nuevo']] ?? $hist->details['status_nuevo'] }})
                                    @elseif(!empty($hist->details))
                                    @endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

        </div>
    </div>
@stop

@section('css')
    <style>
        .invoice { border: 1px solid #dee2e6; }
        .table th, .table td { vertical-align: middle; }
        @media print {
            .no-print { display: none !important; }
            .invoice { border: none; }
            body { visibility: visible !important; }
            .content-wrapper { margin-left: 0px !important; }
            .main-header, .main-sidebar, .main-footer, .content-header { display: none !important; }
            .content { margin: 0 !important; padding: 0 !important; }
            .invoice { width: 100%; margin: 0; padding: 0; border: none; box-shadow: none; }
        }
    </style>
@stop

@section('js')
    <script>
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){ $(this).remove(); });
        }, 4000);
    </script>
@stop