<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClinicalConsultation;
use App\Models\Encounter;

echo "Checking consultation status...\n\n";

$consultationId = '019c2347-4e12-7127-8c24-5278467f71a8';
$encounterId = '019c2336-19f7-723c-a484-ec13deeb839d';

$consultation = ClinicalConsultation::find($consultationId);
$encounter = Encounter::find($encounterId);

if ($consultation) {
    echo "✅ Consultation Found:\n";
    echo "   ID: {$consultation->id}\n";
    echo "   Status: {$consultation->status}\n";
    echo "   Encounter ID: {$consultation->encounter_id}\n";
    echo "   IN_PROGRESS constant: " . ClinicalConsultation::STATUS_IN_PROGRESS . "\n";
    echo "   Status equals IN_PROGRESS: " . ($consultation->status === ClinicalConsultation::STATUS_IN_PROGRESS ? 'YES' : 'NO') . "\n";
    echo "   Status equals 'draft': " . ($consultation->status === 'draft' ? 'YES' : 'NO') . "\n";
    echo "   Status equals 'completed': " . ($consultation->status === 'completed' ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ Consultation not found\n";
}

if ($encounter) {
    echo "\n✅ Encounter Found:\n";
    echo "   ID: {$encounter->id}\n";
    echo "   Status: {$encounter->status}\n";
    
    echo "\n🔍 Checking redirect logic:\n";
    $existingConsultation = $encounter->consultations->first();
    if ($existingConsultation) {
        echo "   Existing consultation: YES\n";
        echo "   Consultation status: {$existingConsultation->status}\n";
        echo "   Status !== IN_PROGRESS: " . ($existingConsultation->status !== ClinicalConsultation::STATUS_IN_PROGRESS ? 'YES (will redirect)' : 'NO (will not redirect)') . "\n";
        
        if ($existingConsultation->status !== ClinicalConsultation::STATUS_IN_PROGRESS) {
            echo "   ❌ REDIRECT WILL HAPPEN to: /doctor/consultation/{$existingConsultation->id}\n";
        }
    } else {
        echo "   Existing consultation: NO\n";
    }
} else {
    echo "❌ Encounter not found\n";
}

echo "\nDone.\n";
