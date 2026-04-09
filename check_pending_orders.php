<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClinicalConsultation;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;

// Find the consultation
$consultation = ClinicalConsultation::find('a15e5424-7540-4d21-84b5-4c616adbf509');

if ($consultation) {
    echo "Consultation ID: " . $consultation->id . "\n";
    echo "Status: " . $consultation->status . "\n";
    
    $encounter = $consultation->encounter;
    echo "Encounter ID: " . $encounter->id . "\n";
    echo "Encounter Status: " . $encounter->status . "\n";
    
    // Check all service orders for this encounter
    $serviceOrders = ServiceOrder::where('encounter_id', $encounter->id)->get();
    echo "\nService Orders Count: " . $serviceOrders->count() . "\n";
    
    foreach ($serviceOrders as $order) {
        echo "\n--- Service Order ID: " . $order->id . " ---\n";
        echo "Order Status: " . $order->status . "\n";
        
        // Check all items in this order
        $items = ServiceOrderItem::where('service_order_id', $order->id)->get();
        echo "Items Count: " . $items->count() . "\n";
        
        foreach ($items as $item) {
            echo "  - Item ID: " . $item->id . ", Status: " . $item->status . "\n";
            echo "    Service: " . ($item->serviceItem->service->name ?? 'N/A') . "\n";
            
            // Check if there are results for this item
            $results = \App\Models\ServiceResult::where('service_order_item_id', $item->id)->get();
            echo "    Results Count: " . $results->count() . "\n";
        }
        
        // Check if order should be marked as completed
        $pendingItems = $items->where('status', '!=', 'completed')->count();
        echo "Pending Items: " . $pendingItems . "\n";
    }
} else {
    echo "Consultation not found\n";
}
