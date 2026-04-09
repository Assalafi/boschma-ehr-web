<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Investigation;

$investigationId = 'a15e5744-513d-4bd9-8cb8-35d3fdbef6b7';
$investigation = Investigation::find($investigationId);

if ($investigation) {
    echo "Investigation ID: {$investigation->id}\n";
    echo "Status: {$investigation->status}\n";
    echo "Type: {$investigation->type}\n";
    echo "Category: {$investigation->category}\n";
    echo "Tests: " . json_encode($investigation->tests) . "\n";
    echo "Notes: {$investigation->notes}\n";
    echo "Created: {$investigation->created_at}\n";
    echo "Updated: {$investigation->updated_at}\n";
    echo "Encounter ID: {$investigation->encounter_id}\n";
} else {
    echo "Investigation not found\n";
}
