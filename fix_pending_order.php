<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceOrder;
use App\Models\Investigation;

// Fix the specific pending ServiceOrder
$orderId = 'a15e5744-4d13-4f04-9484-078d8bab23be';
$order = ServiceOrder::find($orderId);

if ($order) {
    echo "Found ServiceOrder: {$orderId}\n";
    echo "Current status: {$order->status}\n";
    
    // Update to completed
    $order->update(['status' => 'completed']);
    echo "Updated ServiceOrder status to: completed\n";
} else {
    echo "ServiceOrder not found: {$orderId}\n";
}

// Also fix any pending investigations for this encounter
$encounterId = 'a15e5424-7540-4d21-84b5-4c616adbf509';
$investigations = Investigation::where('encounter_id', $encounterId)
    ->where('status', 'pending')
    ->get();

echo "\nChecking investigations for encounter: {$encounterId}\n";
echo "Pending investigations found: {$investigations->count()}\n";

foreach ($investigations as $investigation) {
    echo "Updating Investigation ID: {$investigation->id}, Type: {$investigation->type}\n";
    $investigation->update(['status' => 'completed']);
}

echo "\nDone! All pending orders for this encounter have been marked as completed.\n";
