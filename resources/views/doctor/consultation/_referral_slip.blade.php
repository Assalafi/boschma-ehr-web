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
        }
        .referral-box {
            width: 100%;
            border: 2px solid #000;
            padding: 10px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            border: 1px dashed #888;
            margin: 0 auto 5px auto;
            line-height: 80px;
            text-align: center;
            font-size: 10px;
            color: #888;
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
    <div class="referral-box">
        <!-- Optional Logo / Picture placeholder (you can replace with actual image) -->
        @if($encounter->patient && $encounter->patient->photo)
            <div style="text-align: center; margin-bottom: 5px;">
                <img src="{{ asset('storage/' . $encounter->patient->photo) }}" alt="Patient Photo" style="width: 80px; height: 80px; object-fit: cover;">
            </div>
        @else
            <div class="logo-placeholder">PICTURE</div>
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
