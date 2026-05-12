<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referral Slip</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
        }
        .watermark img {
            width: 150px;
            height: 150px;
        }
        .referral-box {
            width: 100%;
            border: 2px solid #000;
            padding: 10px;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 5px;
        }
        .logo-container img {
            width: 80px;
            height: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 4px 2px;
            vertical-align: top;
        }
        .label-col {
            width: 35%;
            font-weight: bold;
            padding-right: 8px;
            white-space: nowrap;
        }
        .value-col {
            width: 65%;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        .list-item {
            padding-left: 15px;
        }
        .footer {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    @if($logo_base64)
    <div class="watermark">
        <img src="{{ $logo_base64 }}" alt="Watermark">
    </div>
    @endif

    <div class="referral-box">
        <!-- Logo at top -->
        @if($logo_base64)
        <div class="logo-container">
            <img src="{{ $logo_base64 }}" alt="BOSCHMA Logo">
        </div>
        @endif

        <div class="header">BORNO STATE CONTRIBUTORY HEALTHCARE MANAGEMENT AGENCY</div>

        <table>
            <tr>
                <td class="label-col">Authorization Code:</td>
                <td class="value-col">{{ $authorization_code }}</td>
            </tr>
            <tr>
                <td class="label-col">BOSCHMA Number:</td>
                <td class="value-col">{{ $boschma_number }}</td>
            </tr>
            <tr><td colspan="2" style="height: 5px;"></td></tr>
            <tr>
                <td class="label-col">Beneficiary Name:</td>
                <td class="value-col">{{ $beneficiary_name }}</td>
            </tr>
            <tr>
                <td class="label-col">Phone Number:</td>
                <td class="value-col">{{ $phone_number }}</td>
            </tr>
            <tr><td colspan="2" style="height: 5px;"></td></tr>
            <tr>
                <td class="label-col">From Facility:</td>
                <td class="value-col">{{ $from_facility }}</td>
            </tr>
            <tr>
                <td class="label-col">Facility Referred to:</td>
                <td class="value-col">{{ $facility_referred_to }}</td>
            </tr>
        </table>

        @if($presentation)
        <div class="section-title">Presentation:</div>
        @php
            $presentations = explode('*', $presentation);
        @endphp
        @foreach($presentations as $p)
        @if(trim($p))
        <div class="list-item">* {{ trim($p) }}</div>
        @endif
        @endforeach
        @endif

        <table style="margin-top: 6px;">
            <tr>
                <td class="label-col">Clinical Findings:</td>
                <td class="value-col">{{ $clinical_findings ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-col">Investigation:</td>
                <td class="value-col">{{ $investigation ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-col">Diagnosis:</td>
                <td class="value-col">{{ $diagnosis ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-col">Reason for Referral:</td>
                <td class="value-col">{{ $reason_for_referral }}</td>
            </tr>
            <tr>
                <td class="label-col">Treatment Before Referral:</td>
                <td class="value-col">{{ $treatment_before_referral }}</td>
            </tr>
        </table>

        <div class="footer">
            Date: {{ $date ? $date->format('Y-m-d H:i:s') : 'N/A' }}
        </div>
    </div>
</body>
</html>
