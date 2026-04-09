<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Investigation;

$encounterId = 'a15e5424-7540-4d21-84b5-4c616adbf509';

echo "Checking encounter: {$encounterId}\n";

// Get all service orders for this encounter
$serviceOrders = ServiceOrder::where('encounter_id', $encounterId)->get();
echo "\nService Orders found: {$serviceOrders->count()}\n";

foreach ($serviceOrders as $order) {
    echo "\n--- ServiceOrder ID: {$order->id} ---\n";
    echo "Current status: {$order->status}\n";
    
    // Check all items in this order
    $items = ServiceOrderItem::where('service_order_id', $order->id)->get();
    echo "Items count: {$items->count()}\n";
    
    $allCompleted = true;
    foreach ($items as $item) {
        echo "  Item ID: {$item->id}, Status: {$item->status}\n";
        
        // Check if there are results for this item
        $results = \App\Models\ServiceResult::where('service_order_item_id', $item->id)->get();
        echo "    Results count: {$results->count()}\n";
        
        if ($item->status !== 'completed') {
            $allCompleted = false;
        }
    }
    
    // Update order status if all items are completed
    if ($allCompleted && $order->status !== 'completed') {
        $order->update(['status' => 'completed']);
        echo "  → Updated ServiceOrder to 'completed'\n";
    } elseif (!$allCompleted) {
        echo "  → Cannot update - not all items are completed\n";
    }
}

// Also check investigations
$investigations = Investigation::where('encounter_id', $encounterId)->get();
echo "\nInvestigations found: {$investigations->count()}\n";

foreach ($investigations as $investigation) {
    echo "  Investigation ID: {$investigation->id}, Status: {$investigation->status}, Type: {$investigation->type}\n";
    
    // Check if investigation has results (it might use a different field)
    // For now, let's see if we should update it
    if ($investigation->status === 'pending') {
        // You might need to check if results exist before updating
        echo "    → This investigation is still pending\n";
    }
}

echo "\nDone checking!\n";
