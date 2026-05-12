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
            border-bottom: 2px solid #016634;
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .header h1 {
            color: #016634;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 200px;
            height: 200px;
            border: 3px solid #016634;
            border-radius: 50%;
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
            color: #016634;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .field-group .value {
            background: #f8f9fa;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .section {
            background: #f0f9f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #016634;
        }
        .section h3 {
            margin: 0 0 10px 0;
            color: #016634;
            font-size: 16px;
        }
        .auth-code {
            background: #e8f0ee;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #016634;
            margin-bottom: 20px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #016634;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background: #015228;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
        
    <div class="header">
        <div class="watermark"></div>
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
                    <div style="width: 120px; height: 120px; background: #e8f0ee; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #016634;">
                        <span class="material-symbols-outlined" style="font-size: 48px">person</span>
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
