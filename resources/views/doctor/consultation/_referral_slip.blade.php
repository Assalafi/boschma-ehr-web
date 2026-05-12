<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Referral Slip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #000;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #000;
            font-size: 14px;
            margin-top: 5px;
        }
        .referral-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .field-group {
            margin-bottom: 15px;
        }
        .field-group label {
            display: block;
            font-weight: bold;
            color: #000;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .field-group .value {
            background: #fff;
            padding: 8px 12px;
            border: 1px solid #000;
            border-radius: 4px;
            font-size: 14px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .section {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #000;
        }
        .section h3 {
            margin: 0 0 10px 0;
            color: #000;
            font-size: 16px;
        }
        .auth-code {
            background: #fff;
            border: 1px solid #000;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #000;
            font-size: 12px;
            color: #000;
        }
    </style>
</head>
<body>
        
    <div class="header">
        <h1>BOSCHMA</h1>
        <div class="subtitle">Borno State Contributory Healthcare Management Agency</div>
        <div class="subtitle">Patient Referral Slip</div>
    </div>

    <div class="auth-code">
        Authorization Code: {{ $authorization_code }}
    </div>

    <div class="referral-grid">
        <div class="field-group">
            <label>Patient Photo</label>
            <div class="value" style="text-align: center; padding: 10px;">
                @if($encounter->patient && $encounter->patient->photo)
                    <img src="{{ asset('storage/' . $encounter->patient->photo) }}" alt="Patient Photo" style="max-width: 120px; max-height: 120px; border-radius: 8px;">
                @else
                    <div style="width: 120px; height: 120px; background: #fff; border: 1px solid #000; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #000;">
                        <span style="font-size: 48px;">[Photo]</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="field-group">
            <label>BOSCHMA Number</label>
            <div class="value">{{ $boschma_number }}</div>
        </div>
        <div class="field-group full-width">
            <label>Beneficiary Name</label>
            <div class="value">{{ $beneficiary_name }}</div>
        </div>
        <div class="field-group">
            <label>Phone Number</label>
            <div class="value">{{ $phone_number }}</div>
        </div>
        <div class="field-group">
            <label>Date</label>
            <div class="value">{{ $date ? $date->format('Y-m-d H:i:s') : 'N/A' }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Facility Information</h3>
        <div class="referral-grid">
            <div class="field-group">
                <label>From Facility</label>
                <div class="value">{{ $from_facility }}</div>
            </div>
            <div class="field-group">
                <label>Referred To</label>
                <div class="value">{{ $facility_referred_to }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Clinical Information</h3>
        <div class="field-group">
            <label>Presentation (Symptoms)</label>
            <div class="value">{{ $presentation ?: 'N/A' }}</div>
        </div>
        <div class="field-group">
            <label>Clinical Findings</label>
            <div class="value">{{ $clinical_findings ?: 'N/A' }}</div>
        </div>
        <div class="field-group">
            <label>Investigation</label>
            <div class="value">{{ $investigation ?: 'N/A' }}</div>
        </div>
        <div class="field-group">
            <label>Diagnosis</label>
            <div class="value">{{ $diagnosis ?: 'N/A' }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Referral Details</h3>
        <div class="field-group">
            <label>Reason for Referral</label>
            <div class="value">{{ $reason_for_referral }}</div>
        </div>
        <div class="field-group">
            <label>Treatment Before Referral</label>
            <div class="value">{{ $treatment_before_referral }}</div>
        </div>
    </div>

    <div class="footer">
        This is an official BOSCHMA referral document. Valid authorization code required.
    </div>
</body>
</html>
