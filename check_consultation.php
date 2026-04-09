<?php
require_once __DIR__ . '/bootstrap/app.php';

$consultation = App\Models\ClinicalConsultation::find('a15e5424-7540-4d21-84b5-4c616adbf509');

if ($consultation) {
    echo "Consultation ID: " . $consultation->id . "\n";
    echo "Status: " . $consultation->status . "\n";
    
    $encounter = $consultation->encounter;
    echo "Encounter ID: " . $encounter->id . "\n";
    echo "Encounter Status: " . $encounter->status . "\n";
    
    $labOrders = App\Models\ServiceOrder::where('encounter_id', $encounter->id)->get();
    echo "Lab Orders Count: " . $labOrders->count() . "\n";
    
    foreach ($labOrders as $order) {
        echo "Order ID: " . $order->id . ", Status: " . $order->status . ", Service: " . ($order->service->name ?? 'N/A') . "\n";
        // Check if there are any results for this order
        $results = App\Models\Investigation::where('service_order_id', $order->id)->get();
        echo "  - Results Count: " . $results->count() . "\n";
        foreach ($results as $result) {
            echo "    * Result ID: " . $result->id . ", Status: " . $result->status . "\n";
        }
    }
} else {
    echo "Consultation not found\n";
}
