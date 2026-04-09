<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all ServiceOrders to 'completed' if all their items are completed
        $pendingOrders = ServiceOrder::where('status', 'pending')->get();
        
        foreach ($pendingOrders as $order) {
            $allItemsCompleted = $order->items()->where('status', '!=', 'completed')->count() === 0;
            
            if ($allItemsCompleted) {
                $order->update(['status' => 'completed']);
                echo "Updated ServiceOrder {$order->id} to completed\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as this is a data correction
    }
};
