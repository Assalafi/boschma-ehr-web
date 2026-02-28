<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClinicalConsultation;

echo "Checking all consultation statuses in the system...\n\n";

$consultations = ClinicalConsultation::all();
$statuses = [];

foreach ($consultations as $consultation) {
    $status = $consultation->status;
    if (!isset($statuses[$status])) {
        $statuses[$status] = 0;
    }
    $statuses[$status]++;
}

echo "Found statuses:\n";
foreach ($statuses as $status => $count) {
    echo "   - '{$status}': {$count} consultations\n";
}

echo "\nChecking specific consultation 019c2347-4e12-7127-8c24-5278467f71a8:\n";

$specificConsultation = ClinicalConsultation::find('019c2347-4e12-7127-8c24-5278467f71a8');
if ($specificConsultation) {
    echo "   Status: '{$specificConsultation->status}'\n";
    
    $allowedStatuses = ['In Progress', 'draft'];
    echo "   Allowed statuses: " . implode(', ', $allowedStatuses) . "\n";
    echo "   Will redirect: " . (!in_array($specificConsultation->status, $allowedStatuses) ? 'YES' : 'NO') . "\n";
    
    if (!in_array($specificConsultation->status, $allowedStatuses)) {
        echo "   ❌ This status is NOT allowed and will cause redirect!\n";
    } else {
        echo "   ✅ This status is allowed and will NOT redirect\n";
    }
} else {
    echo "   Consultation not found\n";
}

echo "\nDone.\n";
