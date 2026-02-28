<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClinicalConsultation;

echo "Comprehensive consultation status analysis...\n\n";

// Get all unique statuses
$consultations = ClinicalConsultation::all();
$statuses = [];

foreach ($consultations as $consultation) {
    $status = $consultation->status;
    if (!isset($statuses[$status])) {
        $statuses[$status] = 0;
    }
    $statuses[$status]++;
}

echo "📊 ALL CONSULTATION STATUSES FOUND:\n";
echo "================================\n";
foreach ($statuses as $status => $count) {
    echo sprintf("   %-15s: %3d consultations\n", "'{$status}'", $count);
}

echo "\n🔍 STATUS ANALYSIS:\n";
echo "==================\n";

// Check each status
$definedConstants = [
    'In Progress' => ClinicalConsultation::STATUS_IN_PROGRESS,
    'Completed' => ClinicalConsultation::STATUS_COMPLETED,
    'Pending' => ClinicalConsultation::STATUS_PENDING,
];

echo "Defined constants:\n";
foreach ($definedConstants as $name => $value) {
    echo "   - STATUS_" . strtoupper(str_replace(' ', '_', $name)) . " = '{$value}'\n";
}

echo "\nStatus analysis:\n";
foreach ($statuses as $status => $count) {
    $isDefined = in_array($status, $definedConstants);
    $isCurrentlyAllowed = in_array($status, ['In Progress', 'draft', 'active']);
    
    echo sprintf("   %-15s: ", "'{$status}'");
    echo $isDefined ? "✅ Defined" : "❌ Not defined";
    echo " | ";
    echo $isCurrentlyAllowed ? "✅ Allowed" : "❌ Not allowed";
    echo " | ";
    echo "{$count} consultations\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "==================\n";

// Find undefined statuses
$undefinedStatuses = [];
foreach ($statuses as $status => $count) {
    if (!in_array($status, $definedConstants)) {
        $undefinedStatuses[] = $status;
    }
}

if (!empty($undefinedStatuses)) {
    echo "1. Add these constants to ClinicalConsultation model:\n";
    foreach ($undefinedStatuses as $status) {
        $constName = 'STATUS_' . strtoupper(str_replace(' ', '_', $status));
        echo "   const {$constName} = '{$status}';\n";
    }
}

echo "\n2. Recommended redirect logic:\n";
$editableStatuses = [];
foreach ($statuses as $status => $count) {
    if ($status !== 'Completed') {
        $editableStatuses[] = "'{$status}'";
    }
}
echo "   Allowed statuses: " . implode(', ', $editableStatuses) . "\n";

echo "\n3. Impact analysis:\n";
foreach ($statuses as $status => $count) {
    $allowed = ($status !== 'Completed') ? "✅ Allow" : "❌ Redirect";
    echo "   - '{$status}': {$count} consultations -> {$allowed}\n";
}

echo "\n✅ Analysis complete!\n";
