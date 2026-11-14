<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation - {{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
            size: a4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.35;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #BE9223;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #3F527D;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .company-tagline {
            font-size: 8.5pt;
            font-weight: bold;
            color: #374151;
            margin: 2px 0 4px 0;
        }

        .company-details {
            font-size: 8pt;
            color: #1f2937;
            line-height: 1.35;
        }

        .quotation-badge {
            background-color: #3F527D;
            color: #ffffff;
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            padding: 5px 12px;
            letter-spacing: 1px;
            border-radius: 3px;
            display: inline-block;
        }

        .meta-box {
            text-align: right;
            font-size: 8.5pt;
        }

        .meta-box table {
            margin-left: auto;
        }

        .meta-box td {
            padding: 2px 4px;
        }

        .party-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .party-box {
            width: 50%;
            vertical-align: top;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            background-color: #f9fafb;
            font-size: 8.5pt;
        }

        .party-title {
            font-weight: bold;
            color: #3F527D;
            font-size: 9pt;
            border-bottom: 1.5px solid #BE9223;
            padding-bottom: 3px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            background-color: #3F527D;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            padding: 6px 4px;
            border: 1px solid #3F527D;
            text-align: center;
        }

        .items-table td {
            padding: 5px 4px;
            border: 1px solid #9ca3af;
            font-size: 8pt;
            color: #111827;
            vertical-align: top;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }

        /* Summary / Totals */
        .summary-wrapper {
            width: 100%;
            page-break-inside: avoid;
            margin-top: 5px;
        }

        .words-box {
            width: 55%;
            vertical-align: top;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            font-size: 8.5pt;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .totals-table td {
            padding: 3px 6px;
            border: 1px solid #d1d5db;
        }

        .grand-total-row {
            background-color: #3F527D;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .grand-total-row td {
            border: 1px solid #3F527D;
            color: #ffffff;
            padding: 5px 6px;
        }

        /* Terms & Signatures */
        .terms-box {
            width: 100%;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            margin-top: 8px;
            background-color: #f9fafb;
            font-size: 8pt;
            page-break-inside: avoid;
        }

        .terms-title {
            font-weight: bold;
            color: #3F527D;
            margin-bottom: 3px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #BE9223;
            display: inline-block;
            padding-bottom: 2px;
        }

        .bottom-section {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
            border-collapse: collapse;
        }

        .bank-details-cell {
            width: 55%;
            vertical-align: top;
            font-size: 8pt;
            padding: 6px;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
        }

        .signatory-cell {
            width: 45%;
            vertical-align: bottom;
            text-align: right;
            padding: 6px 12px;
            font-size: 8.5pt;
        }

        .jurisdiction-note {
            font-size: 7.5pt;
            color: #6b7280;
            margin-top: 4px;
            font-style: italic;
        }

        .computer-generated {
            text-align: center;
            font-size: 7.5pt;
            color: #6b7280;
            margin-top: 15px;
            border-top: 1px dashed #d1d5db;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 68%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @php
                            $logoData = $company->getLogoDataUri();
                            $logoPath = $company->getLogoFullPath();
                        @endphp
                        @if ($logoData || $logoPath)
                            <td style="width: 38mm; vertical-align: top; padding-right: 10px;">
                                <img src="{{ $logoData ?: 'file:///' . str_replace('\\', '/', $logoPath) }}" 
                                     style="width: 35mm; max-width: 35mm; height: auto; display: block;" 
                                     alt="Logo">
                            </td>
                        @endif
                        <td style="vertical-align: top;">
                            <h1 class="company-name">{{ $company->company_name }}</h1>
                            <div class="company-tagline">{{ $company->tagline }}</div>
                            <div class="company-details">
                                {{ $company->address_line1 }}<br>
                                @if($company->address_line2){{ $company->address_line2 }}, @endif{{ $company->city }} - {{ $company->pincode }}, {{ $company->state }}<br>
                                Ph: {{ $company->phone }} | Email: {{ $company->email }}<br>
                                <strong>GSTIN:</strong> {{ $company->gstin }} | <strong>PAN:</strong> {{ $company->pan }} | <strong>State Code:</strong> {{ $company->state_code }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 32%; vertical-align: top; text-align: right;">
                <div class="quotation-badge">
                    QUOTATION{{ $quotation->revision_number > 0 ? ' (REV R' . $quotation->revision_number . ')' : '' }}
                </div>
                <div class="meta-box" style="margin-top: 8px;">
                    <table align="right">
                        <tr>
                            <td class="text-right font-bold">Quote No:</td>
                            <td class="text-left font-bold" style="color: #3F527D;">{{ $quotation->quotation_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-right font-bold">Date:</td>
                            <td class="text-left">{{ $quotation->quotation_date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-right font-bold">Valid Until:</td>
                            <td class="text-left">{{ $quotation->valid_until->format('d-m-Y') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Parties (Bill To & Ship To) -->
    <table class="party-table">
        <tr>
            <td class="party-box" style="width: 50%;">
                <div class="party-title">Bill To:</div>
                <div class="font-bold" style="font-size: 9.5pt; color: #111827;">{{ $quotation->client_name }}</div>
                <div>{!! nl2br(e($quotation->billing_address)) !!}</div>
                <div style="margin-top: 4px;">
                    <strong>State:</strong> {{ $quotation->state }} (Code: {{ $quotation->state_code }})<br>
                    <strong>GSTIN:</strong> {{ $quotation->gstin ?: 'Unregistered / Retail' }}
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td class="party-box" style="width: 48%;">
                <div class="party-title">Ship To / Site:</div>
                @if ($quotation->shipping_address)
                    <div class="font-bold">{{ $quotation->client_name }}</div>
                    <div>{!! nl2br(e($quotation->shipping_address)) !!}</div>
                @else
                    <div style="color: #6b7280; font-style: italic; padding-top: 8px;">
                        Same as billing address.
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 38%; text-align: left;">Item Description</th>
                <th style="width: 9%;">HSN/SAC</th>
                <th style="width: 6%;">Qty</th>
                <th style="width: 6%;">Unit</th>
                <th style="width: 10%;">Rate (₹)</th>
                <th style="width: 6%;">Disc %</th>
                <th style="width: 10%;">Taxable (₹)</th>
                <th style="width: 6%;">GST %</th>
                <th style="width: 11%;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold" style="color: #111827;">{{ $item->item_name }}</div>
                        @if ($item->description)
                            <div style="font-size: 7.5pt; color: #4b5563; margin-top: 2px;">
                                {!! nl2br(e($item->description)) !!}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->hsn_sac ?: '—' }}</td>
                    <td class="text-center">{{ (float) $item->quantity }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_rate, 2) }}</td>
                    <td class="text-center">{{ $item->discount_percent > 0 ? (float) $item->discount_percent . '%' : '—' }}</td>
                    <td class="text-right">{{ number_format($item->taxable_value, 2) }}</td>
                    <td class="text-center">{{ (float) $item->gst_rate }}%</td>
                    <td class="text-right font-bold">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary & Totals -->
    <table class="summary-wrapper">
        <tr>
            <!-- Amount in Words -->
            <td class="words-box">
                <div class="font-bold" style="color: #3F527D; margin-bottom: 3px; border-bottom: 1.5px solid #BE9223; display: inline-block; padding-bottom: 1px;">Amount Chargeable (in words):</div>
                <div class="font-bold" style="font-size: 9pt; color: #111827;">{{ $quotation->amount_in_words }}</div>
                @if ($quotation->notes)
                    <div style="margin-top: 8px; border-top: 1px dashed #d1d5db; padding-top: 4px;">
                        <strong>Notes:</strong> {{ $quotation->notes }}
                    </div>
                @endif
            </td>
            <td style="width: 2%;"></td>
            <!-- Totals Table -->
            <td style="width: 43%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="font-bold" style="width: 55%;">Subtotal:</td>
                        <td class="text-right" style="width: 45%;">₹ {{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    @if ($quotation->discount_amount > 0)
                        <tr>
                            <td class="font-bold" style="color: #dc2626;">Total Discount:</td>
                            <td class="text-right" style="color: #dc2626;">- ₹ {{ number_format($quotation->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="font-bold">Taxable Amount:</td>
                        <td class="text-right">₹ {{ number_format($quotation->taxable_amount, 2) }}</td>
                    </tr>

                    @if (!$quotation->is_interstate)
                        <!-- Intra-state Assam: CGST + SGST -->
                        <tr>
                            <td>CGST Total:</td>
                            <td class="text-right">₹ {{ number_format($quotation->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST Total:</td>
                            <td class="text-right">₹ {{ number_format($quotation->sgst_amount, 2) }}</td>
                        </tr>
                    @else
                        <!-- Inter-state: IGST -->
                        <tr>
                            <td>IGST Total:</td>
                            <td class="text-right">₹ {{ number_format($quotation->igst_amount, 2) }}</td>
                        </tr>
                    @endif

                    @if ($quotation->round_off != 0)
                        <tr>
                            <td>Round Off:</td>
                            <td class="text-right">{{ $quotation->round_off > 0 ? '+' : '' }}₹ {{ number_format($quotation->round_off, 2) }}</td>
                        </tr>
                    @endif

                    <tr class="grand-total-row">
                        <td>GRAND TOTAL:</td>
                        <td class="text-right">₹ {{ number_format($quotation->grand_total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Terms and Conditions -->
    @if ($quotation->terms_and_conditions)
        <div class="terms-box">
            <div class="terms-title">Terms & Conditions:</div>
            <div>{!! nl2br(e($quotation->terms_and_conditions)) !!}</div>
        </div>
    @endif

    <!-- Bank Details & Signatory (Kept together on last page) -->
    <table class="bottom-section">
        <tr>
            <td class="bank-details-cell">
                <div class="font-bold" style="color: #3F527D; margin-bottom: 2px; border-bottom: 1.5px solid #BE9223; display: inline-block; padding-bottom: 1px;">Bank Details for Remittance:</div><br>
                <strong>Bank Name:</strong> {{ $company->bank_name ?: 'State Bank of India' }}<br>
                <strong>A/C Name:</strong> {{ $company->bank_account_name ?: $company->company_name }}<br>
                <strong>A/C No:</strong> {{ $company->bank_account_no ?: '38472910482' }}<br>
                <strong>IFSC Code:</strong> {{ $company->bank_ifsc ?: 'SBIN0003295' }}<br>
                <strong>Branch:</strong> {{ $company->bank_branch ?: 'G.S. Road Branch, Guwahati' }}
                <div class="jurisdiction-note">{{ $company->jurisdiction_text ?: 'Subject to Guwahati jurisdiction' }}</div>
            </td>
            <td class="signatory-cell">
                <div class="font-bold" style="color: #111827; margin-bottom: 35px;">
                    For {{ strtoupper($company->company_name) }}
                </div>
                @if ($company->show_signature_on_pdf && $company->signature_path)
                    <div style="margin-bottom: 5px;">
                        <img src="{{ public_path($company->signature_path) }}" style="max-height: 45px;" alt="Signature">
                    </div>
                @endif
                <div class="font-bold" style="border-top: 1px solid #374151; display: inline-block; padding-top: 2px; min-width: 150px;">
                    {{ $company->signatory_title ?: 'Authorized Signatory' }}
                </div>
            </td>
        </tr>
    </table>

    @if ($company->show_computer_generated_note)
        <div class="computer-generated">
            This is a computer-generated quotation and does not require a physical signature.
        </div>
    @endif

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 8;
            $color = array(0.4, 0.4, 0.4);
            $y = $pdf->get_height() - 25;
            $x = ($pdf->get_width() / 2) - 25;
            $pdf->text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>
