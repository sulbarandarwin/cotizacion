<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Presupuesto - {{ $quote->quote_number }}</title>
<style>
body {
font-family: 'DejaVu Sans', monospace;
font-size: 9pt;
line-height: 1.2;
color: #000;
}
@page {
margin: 25px 35px 50px 35px; /* top, right, bottom, left - Margen inferior para el pie */
}

    #footer_content {
        position: fixed; 
        bottom: -30px; /* Ajustar para que quede dentro del margen inferior de la página */
        left: 0px; 
        right: 0px; 
        height: 20px; /* Altura del pie de página */
        text-align: right;
        font-size: 8pt;
        color: #555;
    }
    /* Esta pseudo-clase es la que insertará el número de página */
    #footer_content .page-number:after {
        content: counter(page) " de " counter(pages);
    }


    .header-company { text-align: center; margin-bottom: 10px; line-height:1.1; }
    .company-name-main { font-size: 14pt; font-weight: bold; margin: 0; }
    .header-company p { margin: 0px 0; font-size: 8pt; }

    .document-title-box { text-align: center; margin-bottom: 8px; }
    .document-title-text { font-size: 15pt; font-weight: bold; }
    
    .quote-meta-info { overflow: hidden; margin-bottom: 8px; font-size: 8pt; }
    .quote-number-details { float: right; width: 45%; text-align:right; }
    .quote-date-details { float: left; width: 45%; }
    .quote-date-details p, .quote-number-details p { margin: 0px 0; }
    
    .client-info-box { margin-bottom: 8px; padding: 3px 0; font-size: 8.5pt;}
    .client-info-box p { margin: 1px 0; }
    .client-info-box strong { font-weight: bold; }
    
    .separator-line {
        border-top: 1px solid #000;
        margin: 4px 0;
        height: 0px;
        line-height:0px;
    }
    .double-separator-line {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        height: 1px; 
        margin: 6px 0;
    }

    .items-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 8pt; }
    .items-table th, .items-table td { padding: 3px 2px; text-align: left; vertical-align: top; }
    .items-table th { font-weight: bold; }
    
    .items-table .col-item { width: 10%; text-align:left; }
    .items-table .col-description { width: 50%; }
    .items-table .col-quantity { width: 10%; text-align:center;}
    .items-table .col-price { width: 15%; text-align:right;}
    .items-table .col-total { width: 15%; text-align:right;}
    
    .currency-note { font-size: 8pt; margin-top: 3px; margin-bottom:10px; }

    .totals-section { width: 40%; float: right; margin-top: 10px; font-size: 8.5pt; }
    .totals-table { width: 100%; }
    .totals-table td { padding: 1px 3px; }
    .totals-table td.label { text-align: right; font-weight: normal; width:65%;}
    .totals-table td.value { text-align: right; width:35%; font-weight:bold;}
    .totals-table tr.grand-total td { font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-top:2px; padding-bottom:2px;}
    
    .footer-notes-final { margin-top: 15px; font-size: 7.5pt; width:100%; clear:both; padding-top:8px; border-top:1px solid #000;}
    .footer-notes-final p, .footer-notes-final div { margin-bottom: 4px; white-space: pre-wrap; line-height: 1.2; }
    .footer-notes-final strong { font-weight: bold; }

    .clearfix::after { content: ""; clear: both; display: table; }
</style>
</head>
<body>
{{-- DIV para el pie de página --}}
<div id="footer_content">
Pg: <span class="page-number"></span> {{-- El CSS :after se encargará de esto --}}
</div>

<div class="container">
    <div class="header-company">
        <p class="company-name-main">{{ $companySettings['company_name'] ?? 'NOMBRE DE EMPRESA' }}</p>
        <p>{{ $companySettings['company_address'] ?? 'Dirección Fiscal de la Empresa' }}</p>
        <p>Telfs: {{ $companySettings['company_phone'] ?? '0000-0000000' }} - RIF: {{ $companySettings['company_rif'] ?? 'J-00000000-0' }}
        @if(!empty($companySettings['company_nit']))
        - NIT: {{ $companySettings['company_nit'] }}
        @endif
        </p>
    </div>

    <div class="document-title-box">
        <span class="document-title-text">PRESUPUESTO</span>
    </div>

    <div class="quote-meta-info clearfix">
        <div class="quote-date-details">
             <p><strong>Fecha:</strong> {{ $quote->issue_date ? $quote->issue_date->format('d/m/Y') : 'N/A' }}</p>
             <p><strong>Vence:</strong> {{ $quote->expiry_date ? $quote->expiry_date->format('d/m/Y') : 'N/A' }}</p>
        </div>
        <div class="quote-number-details">
            <p><strong>N°:</strong> {{ $quote->quote_number }}</p>
            <p><strong>Condición:</strong> {{ $companySettings['default_payment_condition'] ?? 'CONTADO' }}</p>
        </div>
    </div>
    
    <div class="client-info-box">
        <p><strong>Cliente:</strong> {{ $quote->client->name ?? 'N/A' }}</p>
        <p><strong>RIF/C.I.:</strong> {{ $quote->client->identifier ?? 'N/A' }}</p>
        <p><strong>Dirección:</strong> {{ Str::limit($quote->client->address ?? 'N/A', 150) }}</p>
        <p><strong>Teléfonos:</strong> {{ $quote->client->phone ?? 'N/A' }}</p>
        <p><strong>Vendedor:</strong> {{ $quote->user->name ?? 'N/A' }}</p>
    </div>
    
    <div class="double-separator-line"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="col-item">Ítem</th>
                <th class="col-description">Descripción</th>
                <th class="col-quantity">Cant.</th>
                <th class="col-price">P.Unit ({{$quote->base_currency}})</th>
                <th class="col-total">Total ({{$quote->base_currency}})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $index => $item)
            <tr>
                <td class="col-item">{{ $index + 1 }}</td>
                <td class="col-description">
                    {{ $item->product->name ?? $item->manual_product_name }}
                    @if($item->product_id && $item->product && $item->product->code) 
                        <span style="font-size:7pt; color:#444;">(Cód.Prod: {{ $item->product->code }})</span> 
                    @endif
                </td>
                <td class="col-quantity">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                <td class="col-price">{{ number_format($item->price, 2, ',', '.') }}</td>
                <td class="col-total">{{ number_format($item->line_total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="separator-line" style="margin-top:0px; margin-bottom:5px;"></div>
    
    <div class="currency-note">
        Moneda de Referencia: {{ $quote->base_currency }}
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">{{ number_format($quote->subtotal, 2, ',', '.') }}</td>
            </tr>
            @if(isset($quote->discount_amount) && (float)$quote->discount_amount > 0)
            <tr>
                <td class="label">Descuento @if($quote->discount_type == 'percentage') ({{ rtrim(rtrim(number_format($quote->discount_value, 2, ',', '.'), '0'), ',') }}%) @endif:</td>
                <td class="value">-{{ number_format($quote->discount_amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Base Imponible:</td>
                <td class="value">{{ number_format($quote->subtotal - $quote->discount_amount, 2, ',', '.') }}</td>
            </tr>
            @else
             <tr>
                <td class="label">Base Imponible:</td>
                <td class="value">{{ number_format($quote->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">{{ $companySettings['tax_label'] ?? 'I.V.A.' }} ({{ rtrim(rtrim(number_format($quote->tax_percentage, 2, ',', '.'),'0'),',') }}%):</td>
                <td class="value">{{ number_format($quote->tax_amount, 2, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td class="label">TOTAL {{ $quote->base_currency }}:</td>
                <td class="value">{{ number_format($quote->total, 2, ',', '.') }}</td>
            </tr>

            @if($quote->base_currency === 'USD')
                @php
                    $totalInBs = null; $rateUsed = null; $rateType = '';
                    if (isset($quote->exchange_rate_bcv) && (float)$quote->exchange_rate_bcv > 0) {
                        $totalInBs = $quote->total * (float)$quote->exchange_rate_bcv;
                        $rateUsed = (float)$quote->exchange_rate_bcv;
                        $rateType = 'BCV';
                    } elseif (isset($quote->exchange_rate_promedio) && (float)$quote->exchange_rate_promedio > 0) {
                        $totalInBs = $quote->total * (float)$quote->exchange_rate_promedio;
                        $rateUsed = (float)$quote->exchange_rate_promedio;
                        $rateType = 'Promedio';
                    }
                @endphp
                @if($totalInBs && $rateUsed)
                <tr style="font-size:8pt;">
                    <td class="label" style="font-weight:normal;">Total Bs. (Tasa {{ $rateType }} {{ number_format($rateUsed, 2, ',', '.') }}):</td>
                    <td class="value">{{ number_format($totalInBs, 2, ',', '.') }}</td>
                </tr>
                @endif
            @endif
        </table>
    </div>
    <div class="clearfix"></div>

    <div class="footer-notes-final">
        @if($quote->terms_and_conditions)
            <div class="section-block">
                <p><strong>Términos y Condiciones:</strong> {!! nl2br(e($quote->terms_and_conditions)) !!}</p>
            </div>
        @else
            <div class="section-block">
                 <p><strong>Términos y Condiciones:</strong> Presupuesto sujeto a cambio sin previo aviso. Validez de la oferta: {{ $quote->expiry_date ? $quote->issue_date->diffInDays($quote->expiry_date) + 1 : ($companySettings['default_validity_days'] ?? '15') }} días.</p>
            </div>
        @endif

        @if(!empty($companySettings['payment_bank_details']) || !empty($companySettings['payment_other_methods']))
        <div class="section-block">
            <p><strong>Información de Pago:</strong></p>
            @if(!empty($companySettings['payment_bank_details']))
                <div>
                    <strong>Cuentas Bancarias:</strong><br>
                    {!! nl2br(e($companySettings['payment_bank_details'])) !!}
                </div>
            @endif
            @if(!empty($companySettings['payment_other_methods']))
                <div style="margin-top: {{ !empty($companySettings['payment_bank_details']) ? '5px' : '0' }};">
                    <strong>Otros Métodos de Pago:</strong><br>
                    {!! nl2br(e($companySettings['payment_other_methods'])) !!}
                </div>
            @endif
        </div>
        @endif

        @if($quote->notes_to_client)
            <div class="section-block">
                <p><strong>Observaciones:</strong></p>
                <p>{!! nl2br(e($quote->notes_to_client)) !!}</p>
            </div>
        @endif
    </div>
</div>

{{-- Se elimina el script PHP de numeración, se usará CSS --}}

<script type="text/php">
        if (isset($pdf) && $pdf->getCanvas()) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 8;
            $color = array(0.33, 0.33, 0.33);
            $pageWidth = $pdf->get_canvas()->get_width();
            $pageHeight = $pdf->get_canvas()->get_height(); // Altura del área de contenido
            $pageText = "Pg: " . $pdf->get_canvas()->get_page_number() . " de " . $pdf->get_canvas()->get_page_count();
            $textWidth = $fontMetrics->getTextWidth($pageText, $font, $size);
            
            // Posición X: Alineado a la derecha con margen
            $x = $pageWidth - $textWidth - 35; // 35pt es el margen derecho de @page
            
            // Posición Y: En el margen inferior físico
            // Si @page margin-bottom es 50px, y el texto tiene ~10px de alto, 
            // queremos colocarlo ~35px desde el borde inferior del papel.
            $y = $pageHeight + 35; // Ajustar este valor: 50px (margen) - 15px (espacio deseado desde abajo del margen)
                                   // O más directo: $pdf->get_height() - 35 (desde el borde físico del papel)

            $pdf->get_canvas()->text($x, $y, $pageText, $font, $size, $color);
        }
    </script>
    
</body>
</html>
</body>
</html>

