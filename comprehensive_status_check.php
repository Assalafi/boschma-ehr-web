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
$statusDetails = [];

foreach ($consultations as $consultation) {
    $status = $consultation->status;
    if (!isset($statuses[$status])) {
        $statuses[$status] = 0;
        $statusDetails[$status] = [];
    }
    $statuses[$status]++;
    $statusDetails[$status][] = [
        'id' => $consultation->id,
        'encounter_id' => $consultation->encounter_id,
        'created_at' => $consultation->created_at,
        'updated_at' => $consultation->updated_at
    ];
}

echo "📊 ALL CONSULTATION STATUSES FOUND:\n";
echo "================================\n";
foreach ($statuses as $status => $count) {
    echo sprintf("   %-15s: %3d consultations\n", "'{$status}'", $count);
}

echo "\n🔍 DETAILED ANALYSIS:\n";
echo "====================\n";

// Check each status against current constants
$definedConstants = [
    'In Progress' => ClinicalConsultation::STATUS_IN_PROGRESS,
    'Completed' => ClinicalConsultation::STATUS_COMPLETED,
    'Pending' => ClinicalConsultation::STATUS_PENDING,
];

echo "Defined constants in ClinicalConsultation model:\n";
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
    
    if (!$isDefined) {
        echo "     ⚠️  Should add STATUS_" . strtoupper(str_replace(' ', '_', $status)) . " = '{$status}'\n";
    }
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "==================\n";

$undefinedStatuses = array_diff(array_keys($statuses), $definedConstants);
if (!empty($undefinedStatuses)) {
    echo "1. Add these constants to ClinicalConsultation model:\n";
    foreach ($undefinedStatuses as $status) {
        $constName = 'STATUS_' . strtoupper(str_replace(' ', '_', $status));
        echo "   const {$constName} = '{$status}';\n";
    }
}

echo "\n2. Update redirect logic to allow all editable statuses:\n";
$editableStatuses = array_diff(array_keys($statuses), ['Completed']);
echo "   Allowed statuses: " . implode(', ', array_map(function($s) { return "'{$s}'"; }, $editableStatuses)) . "\n";

echo "\n3. Current redirect logic will allow:\n";
foreach ($editableStatuses as $status) {
    $count = isset($statuses[$status]) ? $statuses[$status] : 0;
    echo "   - '{$status}': {$count} consultations ✅\n";
}

$completedCount = isset($statuses['Completed']) ? $statuses['Completed'] : 0;
echo "\n   Will redirect only 'Completed': {$completedCount} consultations ❌\n";

echo "\n✅ Analysis complete!\n";
