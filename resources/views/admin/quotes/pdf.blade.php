{{-- resources/views/admin/quotes/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Presupuesto - {{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px; /* Reducir tamaño base para más semejanza */
            line-height: 1.3;
            color: #000; /* Texto negro como en el ejemplo */
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 40px 50px; /* Margen de la página */
        }
        .container {
            width: 100%;
        }
        .header-company-info {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .company-details {
            font-size: 9px;
            line-height: 1.2;
            margin-bottom: 5px;
        }
        .document-title-section {
            text-align: right;
            float: right; /* Alinea a la derecha */
            width: 40%;  /* Ajusta el ancho */
            margin-top: -60px; /* Sube esta sección si el logo/nombre de empresa es alto */
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 2px;
            display: inline-block; /* Para que el borde se ajuste al texto */
        }
        .quote-number-box {
            font-size: 14px;
            border: 1px solid #000;
            padding: 3px;
            margin-bottom: 2px;
            display: inline-block;
        }
        .payment-terms-box {
            font-size: 10px;
            border: 1px solid #000;
            padding: 3px;
            display: inline-block;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 3px 0px; /* Menos padding vertical */
            vertical-align: top;
            font-size: 9.5px;
        }
        .label {
            font-weight: bold;
            white-space: nowrap; /* Evita que la etiqueta se parta */
            padding-right: 5px;
        }
        .client-info-left { width: 65%; }
        .client-info-right { width: 35%; text-align:left; } /* Alineado a la izquierda como en el ejemplo */
        
        .page-number {
            text-align: right;
            font-size: 9px;
            margin-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-top: 1px solid #000; /* Línea superior como en el ejemplo */
            border-bottom: 1px solid #000; /* Línea inferior */
        }
        .items-table th, .items-table td {
            border: none; /* Sin bordes internos en celdas */
            border-bottom: 1px dotted #ccc; /* Línea punteada suave entre filas */
            padding: 4px;
            text-align: left;
            font-size: 9.5px; /* Tamaño de fuente de la tabla */
        }
        .items-table th {
            border-bottom: 1px solid #000; /* Encabezado con línea sólida abajo */
            font-weight: bold;
            white-space: nowrap;
        }
        .items-table td:nth-child(1) { width: 8%; }  /* Código */
        .items-table td:nth-child(2) { width: 42%; } /* Descripción */
        .items-table td:nth-child(3) { width: 10%; text-align: center; } /* Cant. */
        .items-table td:nth-child(4) { width: 15%; text-align: right;} /* P.($) */
        .items-table td:nth-child(5) { width: 15%; text-align: right;} /* Total */
        .items-table tr:last-child td {
            border-bottom: none; /* Quitar borde inferior de la última fila de ítems */
        }


        .totals-section {
            width: 45%; /* Ajusta según necesidad */
            float: right;
            margin-top: 10px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table th, .totals-table td {
            padding: 3px 5px;
            text-align: right;
            font-size: 10px;
            border: 1px solid #000; /* Todas las celdas con borde */
        }
        .totals-table th {
            text-align: left; /* Etiquetas de totales a la izquierda */
            font-weight: bold;
            background-color: #f2f2f2; /* Sombreado ligero para encabezados de totales */
        }
        .grand-total th, .grand-total td {
            font-weight: bold;
            font-size: 11px;
        }

        .footer-notes {
            margin-top: 20px;
            font-size: 9px;
            page-break-inside: avoid;
        }
        .notes-terms-pdf {
            font-size: 9px;
            padding: 8px;
            border: 1px solid #ccc;
            margin-top: 10px;
            background-color: #fdfdfd;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        /* Líneas separadoras como en el ejemplo */
        .separator-line {
            border-top: 1px solid #000;
            height: 1px;
            margin: 2px 0;
            width: 100%;
        }

        /* Estilos para el encabezado fijo con logo si se implementa con dompdf */
        /* #header_content { position: fixed; top: -30px; left: 0px; right: 0px; } */
        /* #footer_content { position: fixed; bottom: -30px; left: 0px; right: 0px; text-align: center; font-size: 9px; } */

    </style>
</head>
<body>
    {{-- Script para paginación si se usa un pie de página fijo con DOMPDF --}}
    {{-- <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $width = $fontMetrics->getTextWidth($text, $font, $size) / 2;
            $x = ($pdf->get_canvas()->get_width() - $width) / 2 + $width - 20; // Alineado a la derecha
            $y = $pdf->get_canvas()->get_height() - 35;
            $pdf->get_canvas()->text($x, $y, $text, $font, $size);
        }
    </script> --}}

    <div class="container">
        {{-- Encabezado de la empresa --}}
        <div class="header-company-info">
            @if(isset($companySettings['company_logo_base64']))
                <img src="{{ $companySettings['company_logo_base64'] }}" alt="Logo" style="max-height: 60px; margin-bottom: 5px;">
            @else
                <div class="company-name">{{ $companySettings['company_name'] ?? 'NOMBRE DE EMPRESA' }}</div>
            @endif
            <div class="company-details">
                {{ $companySettings['company_address'] ?? 'Dirección de la Empresa, Ciudad, Estado' }}<br>
                TLF: {{ $companySettings['company_phone'] ?? 'N/A' }}
                @if(isset($companySettings['company_fax'])) {{-- Asumiendo que podrías tener un campo FAX --}}
                    - FAX: {{ $companySettings['company_fax'] }}
                @endif
                <br>
                @if(isset($companySettings['company_nit'])) {{-- Asumiendo campo NIT --}}
                    NIT: {{ $companySettings['company_nit'] }}
                @endif
            </div>
            <div>RIF: {{ $companySettings['company_rif'] ?? 'J-00000000-0' }}</div>
        </div>

        <div class="document-title-section">
            <div class="document-title">PRESUPUESTO</div><br>
            <div class="quote-number-box"># {{ $quote->quote_number }}</div><br>
            <div class="payment-terms-box">{{ $quote->payment_condition ?? 'CONTADO' }}</div> {{-- Asumir 'CONTADO' o un nuevo campo en $quote --}}
        </div>
        <div class="clearfix" style="margin-bottom: 10px;"></div>

        {{-- Información de Cliente, Fechas y Vendedor --}}
        <table class="info-grid">
            <tr>
                <td class="client-info-left">
                    <span class="label">Cliente:</span> {{ $quote->client->name }}<br>
                    <span class="label">RIF/Cédula:</span> {{ $quote->client->identifier }}<br>
                    <span class="label">Dirección:</span> {{ $quote->client->address ?? 'N/A' }}<br>
                    <span class="label">Teléfonos:</span> {{ $quote->client->phone ?? 'N/A' }}
                </td>
                <td class="client-info-right">
                    <span class="label">Fecha Emisión:</span> {{ $quote->issue_date->format('d/m/Y') }}<br>
                    <span class="label">Fecha Vencimiento:</span> {{ $quote->expiry_date->format('d/m/Y') }}<br>
                    <span class="label">Vendedor:</span> {{ $quote->user->vendor_code ?? '' }} {{ $quote->user->name ?? 'N/A' }} {{-- Asumiendo campo 'vendor_code' en User --}}
                </td>
            </tr>
        </table>

        <div class="page-number">Pg: 1</div> {{-- Paginación simple por ahora, dompdf puede manejarlo mejor con script --}}
        <div class="separator-line"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">P.({{ $quote->base_currency }})</th> {{-- Usar la moneda base de la cotización --}}
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $itemCounter = 0; @endphp
                @foreach($quote->items as $index => $item)
                @php $itemCounter++; @endphp
                <tr>
                    <td>{{ $item->product->code ?? ($item->manual_product_code ?? 'N/A') }}</td> {{-- Asumiendo product.code o manual_product_code --}}
                    <td>{{ $item->manual_product_name ?: ($item->product ? $item->product->name : 'N/A') }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->line_total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                {{-- Rellenar con filas vacías si son menos de X ítems para mantener altura (opcional) --}}
                @for ($i = $itemCounter; $i < 10; $i++) {{-- Ejemplo: rellenar hasta 10 filas --}}
                {{-- <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> --}}
                @endfor
            </tbody>
        </table>
        <div class="separator-line" style="margin-bottom: 5px;"></div>
        Moneda: {{ $quote->base_currency }}
        <div class="clearfix"></div>


        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <th>Subtotal</th>
                    <td>{{ number_format($quote->subtotal, 2, ',', '.') }}</td>
                </tr>
                @if(isset($quote->discount_amount) && $quote->discount_amount > 0)
                <tr>
                    <th>Descuento @if($quote->discount_type == 'percentage') ({{ number_format($quote->discount_value,0,',','.')}}%) @endif</th>
                    <td class="text-danger">{{ number_format($quote->discount_amount, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <th>Base Imponible</th>
                    <td>{{ number_format($quote->subtotal - ($quote->discount_amount ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>IVA ({{ number_format($quote->tax_percentage, 0, ',', '.') }}%)</th> {{-- IVA sin decimales en % --}}
                    <td>{{ number_format($quote->tax_amount, 2, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <th>TOTAL GENERAL</th>
                    <td>{{ number_format($quote->total, 2, ',', '.') }} {{ $quote->base_currency }}</td>
                </tr>
                 @if($quote->base_currency === 'USD' && $quote->total > 0)
                    @php
                        $totalInBs = null;
                        $rateUsed = null;
                        $rateType = '';
                        if ($quote->exchange_rate_bcv && $quote->exchange_rate_bcv > 0) {
                            $totalInBs = $quote->total * $quote->exchange_rate_bcv;
                            $rateUsed = $quote->exchange_rate_bcv;
                            $rateType = 'BCV';
                        } elseif ($quote->exchange_rate_promedio && $quote->exchange_rate_promedio > 0) {
                            $totalInBs = $quote->total * $quote->exchange_rate_promedio;
                            $rateUsed = $quote->exchange_rate_promedio;
                            $rateType = 'Promedio';
                        }
                    @endphp
                    @if($totalInBs)
                    <tr>
                        <th colspan="2" style="text-align:center; font-size:9px; background-color: #fff; border-top: 1px solid #000;">
                            Total Bs. (Tasa {{ $rateType }} {{ number_format($rateUsed, 2, ',', '.') }}): {{ number_format($totalInBs, 2, ',', '.') }}
                        </th>
                    </tr>
                    @endif
                @endif
            </table>
        </div>
        <div class="clearfix"></div>

        <div class="footer-notes">
            @if($quote->terms_and_conditions)
                <div class="notes-terms-pdf">
                    <strong>Términos y Condiciones:</strong><br>
                    {!! nl2br(e($quote->terms_and_conditions)) !!}
                </div>
            @endif
            @if($quote->notes_to_client)
                <div class="notes-terms-pdf" style="margin-top: 5px;">
                    <strong>Notas Adicionales:</strong><br>
                    {!! nl2br(e($quote->notes_to_client)) !!}
                </div>
            @endif
        </div>
    </div>
</body>
</html>