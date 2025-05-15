{{-- resources/views/admin/quotes/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle de Cotización - ' . $quote->quote_number)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detalle de Cotización: <strong>{{ $quote->quote_number }}</strong></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Cotizaciones</a></li>
                <li class="breadcrumb-item active">{{ $quote->quote_number }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="invoice p-3 mb-3">
        {{-- Acciones Principales --}}
        <div class="row no-print mb-3">
            <div class="col-12">
                <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                @can('edit_quotes')
                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-info"><i class="fas fa-edit"></i> Editar</a>
                @endcan
                <button type="button" onclick="window.print();" class="btn btn-primary float-right" style="margin-right: 5px;">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <div class="btn-group float-right" style="margin-right: 5px;">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                    <div class="dropdown-menu">
                        {{-- BOTÓN ACTUALIZADO --}}
                        <a class="dropdown-item" href="{{ route('quotes.downloadPdf', $quote) }}">PDF</a>
                        <a class="dropdown-item" href="#" onclick="alert('Funcionalidad Descargar Excel pendiente');">Excel</a>
                    </div>
                </div>
                <form action="{{ route('quotes.duplicate', $quote) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-warning float-right" style="margin-right: 5px;" onclick="return confirm('¿Está seguro de que desea duplicar esta cotización? Se creará una nueva cotización editable.')" title="Duplicar Cotización">
                    <i class="fas fa-copy"></i> Duplicar
                </button>
                </form>
                 <button type="button" class="btn btn-default float-right" style="margin-right: 5px;" onclick="alert('Funcionalidad Enviar por Email pendiente');">
                    <i class="fas fa-envelope"></i> Enviar por Email
                </button>
            </div>
        </div>

        {{-- Cabecera con Información de la Empresa y Cliente --}}
        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                <strong>De:</strong>
                <address>
                    <strong>{{ $companySettings['company_name'] ?? 'Nombre de tu Empresa' }}</strong><br>
                    RIF: {{ $companySettings['company_rif'] ?? 'N/A' }}<br>
                    {{ $companySettings['company_address'] ?? 'Dirección no disponible' }}<br>
                    Teléfono: {{ $companySettings['company_phone'] ?? 'N/A' }}<br>
                    Email: {{ $companySettings['company_email'] ?? 'N/A' }}
                    @if(isset($companySettings['company_logo']) && $companySettings['company_logo'])
                        <br><img src="{{ asset('storage/' . $companySettings['company_logo']) }}" alt="Logo Empresa" style="max-height: 70px; margin-top: 10px;">
                    @endif
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <strong>Para:</strong>
                <address>
                    <strong>{{ $quote->client->name }}</strong><br>
                    RIF/Cédula: {{ $quote->client->identifier }}<br>
                    {{ $quote->client->address ?? 'Dirección no especificada' }}<br>
                    Teléfono: {{ $quote->client->phone ?? 'N/A' }}<br>
                    Email: {{ $quote->client->email ?? 'N/A' }}
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <b>Cotización #{{ $quote->quote_number }}</b><br>
                <br>
                <b>Fecha Emisión:</b> {{ $quote->issue_date->format('d/m/Y') }}<br>
                <b>Fecha Vencimiento:</b> {{ $quote->expiry_date->format('d/m/Y') }}<br>
                <b>Estado:</b> <span class="{{ $quote->status_class }}">{{ $quote->status_text }}</span><br>
                <b>Vendedor:</b> {{ $quote->user->name ?? 'N/A' }}<br>
                <b>Moneda Base:</b> {{ $quote->base_currency }}
            </div>
        </div>
        <hr>

        {{-- Tabla de Ítems --}}
        <div class="row">
            <div class="col-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 30%">Producto/Servicio</th>
                            <th class="text-center" style="width: 10%">Cantidad</th>
                            <th class="text-right" style="width: 12%">Costo Unit.</th>
                            <th class="text-center" style="width: 15%">Cálculo Precio</th>
                            <th class="text-right" style="width: 13%">Precio Unit.</th>
                            <th class="text-right" style="width: 15%">Subtotal Ítem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->manual_product_name ?: ($item->product ? $item->product->name : 'N/A') }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-right">{{ number_format($item->cost, 2) }} {{ $quote->base_currency }}</td>
                            <td class="text-center">
                                @if($item->price_calculation_method)
                                    <span class="badge badge-info">{{ strtoupper($item->price_calculation_method) }}</span>
                                    @if($item->applied_rate_value)
                                     (@ {{ number_format($item->applied_rate_value, 2) }})
                                    @endif
                                @else
                                    <span class="badge badge-secondary">Manual</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($item->price, 2) }} {{ $quote->base_currency }}</td>
                            <td class="text-right">{{ number_format($item->line_total, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <hr>

        {{-- Totales y Notas/Términos --}}
        <div class="row">
            <div class="col-md-6">
                @if($quote->terms_and_conditions)
                    <p class="lead">Términos y Condiciones:</p>
                    <div class="text-muted well well-sm shadow-none" style="margin-top: 10px; padding:10px; border:1px solid #ddd; border-radius: 4px; font-size: 0.9em;">
                        {!! nl2br(e($quote->terms_and_conditions)) !!}
                    </div>
                @endif
                @if($quote->notes_to_client)
                    <p class="lead mt-3">Notas para el Cliente:</p>
                     <div class="text-muted well well-sm shadow-none" style="margin-top: 10px; padding:10px; border:1px solid #ddd; border-radius: 4px; font-size: 0.9em;">
                        {!! nl2br(e($quote->notes_to_client)) !!}
                    </div>
                @endif
                @if($quote->internal_notes && (auth()->user()->can('view_internal_notes_quotes') || auth()->user()->id == $quote->user_id) )
                    <p class="lead mt-3">Notas Internas (No visibles para el cliente):</p>
                     <div class="text-muted well well-sm shadow-none" style="margin-top: 10px; padding:10px; border:1px solid #eee; background-color: #f9f9f9; border-radius: 4px; font-size: 0.9em;">
                        {!! nl2br(e($quote->internal_notes)) !!}
                    </div>
                @endif

                @if($quote->auto_save_data && ($quote->status === 'Borrador') && auth()->user()->can('edit_quotes'))
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i> Esta cotización tiene datos autoguardados que podrían ser más recientes.
                        <a href="{{ route('quotes.edit', $quote) }}?restore_autosave=true" class="btn btn-xs btn-primary ml-2">Restaurar y Editar</a>
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <p class="lead">Resumen Financiero</p>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th style="width:50%">Subtotal Bruto:</th>
                            <td class="text-right">{{ number_format($quote->subtotal, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        @if(isset($quote->discount_value) && $quote->discount_value > 0)
                        <tr>
                            <th>
                                Descuento Global
                                @if($quote->discount_type == 'percentage')
                                    ({{ number_format($quote->discount_value, 2) }}%)
                                @else
                                    (Monto Fijo)
                                @endif
                                :
                            </th>
                            <td class="text-right text-danger">- {{ number_format($quote->discount_amount, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        <tr>
                            <th>Subtotal Neto:</th>
                            <td class="text-right">{{ number_format($quote->subtotal - $quote->discount_amount, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Impuestos ({{ number_format($quote->tax_percentage, 2) }}%):</th>
                            <td class="text-right">{{ number_format($quote->tax_amount, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        <tr class="font-weight-bold" style="font-size: 1.1em;">
                            <th>Total:</th>
                            <td class="text-right">{{ number_format($quote->total, 2) }} {{ $quote->base_currency }}</td>
                        </tr>
                        @if($quote->base_currency === 'USD')
                            @if($quote->exchange_rate_bcv && $quote->exchange_rate_bcv > 0)
                            <tr>
                                <th>Total Aprox. en BS (Tasa BCV {{ number_format($quote->exchange_rate_bcv, 4) }}):</th>
                                <td class="text-right"><strong>{{ number_format($quote->total * $quote->exchange_rate_bcv, 2) }}</strong></td>
                            </tr>
                            @endif
                            @if($quote->exchange_rate_promedio && $quote->exchange_rate_promedio > 0 && $quote->exchange_rate_promedio != $quote->exchange_rate_bcv)
                            <tr>
                                <th>Total Aprox. en BS (Tasa Promedio {{ number_format($quote->exchange_rate_promedio, 4) }}):</th>
                                <td class="text-right"><strong>{{ number_format($quote->total * $quote->exchange_rate_promedio, 2) }}</strong></td>
                            </tr>
                            @endif
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <hr>

        {{-- Historial de Cambios --}}
        @if($quote->history && $quote->history->count() > 0 && auth()->user()->can('view_quote_history'))
        <div class="row mt-4">
            <div class="col-12">
                <p class="lead">Historial de Cambios Recientes (Últimos 5):</p>
                <ul class="list-unstyled" style="font-size: 0.9em;">
                    @foreach($quote->history->take(5) as $history_entry)
                        <li>
                            <i class="fas fa-history text-muted"></i>
                            <strong>{{ $history_entry->created_at->format('d/m/Y H:i') }}</strong> -
                            {{ $history_entry->user->name ?? 'Sistema' }}:
                            <em>{{ $history_entry->action }}</em>.
                            
                            @if (!empty($history_entry->details) && is_array($history_entry->details))
                                <small class="text-muted">
                                    (
                                    @foreach ($history_entry->details as $detailKey => $detailValue)
                                        @if(is_string($detailKey) && (Str::startsWith($detailKey, 'item_actualizado_') || Str::startsWith($detailKey, 'item_nuevo_') || Str::startsWith($detailKey, 'item_eliminado_')))
                                            @php
                                                $actionParts = explode('_', $detailKey);
                                                $itemAction = $actionParts[0] . ' ' . $actionParts[1];
                                                $itemIdSuffix = $actionParts[2] ?? '';
                                            @endphp
                                            <span style="display: block; margin-left: 15px;">
                                                <em>{{ ucfirst($itemAction) }} {{ $itemIdSuffix ? '(ID: '.$itemIdSuffix.')' : '' }}:</em>
                                                @if(is_array($detailValue))
                                                    @foreach($detailValue as $itemField => $itemVal)
                                                        <span style="display: block; margin-left: 30px;">
                                                            {{ ucfirst(str_replace('_', ' ', $itemField)) }}:
                                                            @if(is_array($itemVal) && isset($itemVal['anterior']) && isset($itemVal['nuevo']))
                                                                "{{ Str::limit($itemVal['anterior'], 30) }}" &rarr; "{{ Str::limit($itemVal['nuevo'], 30) }}"
                                                            @elseif(is_array($itemVal))
                                                                {{ Str::limit(json_encode($itemVal), 50) }}
                                                            @else
                                                                "{{ Str::limit($itemVal, 50) }}"
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                @else
                                                   "{{ Str::limit($detailValue, 50) }}"
                                                @endif
                                            </span>
                                        @else
                                        {{ ucfirst(str_replace('_', ' ', $detailKey)) }}: 
                                            @if(is_array($detailValue) && isset($detailValue['anterior']) && isset($detailValue['nuevo']))
                                                "{{ Str::limit($detailValue['anterior'], 30) }}" &rarr; "{{ Str::limit($detailValue['nuevo'], 30) }}"
                                            @elseif(is_array($detailValue))
                                                {{ Str::limit(json_encode($detailValue), 50) }}
                                            @else
                                                "{{ Str::limit($detailValue, 50) }}"
                                            @endif
                                            @if (!$loop->last), @endif
                                        @endif
                                    @endforeach
                                    )
                                </small>
                            @elseif (!empty($history_entry->details) && is_string($history_entry->details))
                                <small class="text-muted"> ({{ Str::limit($history_entry->details, 100) }})</small>
                            @endif
                        </li>
                    @endforeach
                    @if($quote->history->count() > 5)
                        <li><a href="#">Ver historial completo...</a></li>
                    @endif
                </ul>
            </div>
        </div>
        @endif

    </div>
@stop

@section('css')
    <style>
        .invoice {
            border: 1px solid #dee2e6;
            background-color: #fff;
        }
        .well {
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            padding: 10px;
            box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            //  Futuro JS aquí
        });
    </script>
@stop