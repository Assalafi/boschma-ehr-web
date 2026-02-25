<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_dispensations', function (Blueprint $table) {
            if (!Schema::hasColumn('pharmacy_dispensations', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('cost_of_medication');
            }
            if (!Schema::hasColumn('pharmacy_dispensations', 'copayment_amount')) {
                $table->decimal('copayment_amount', 10, 2)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_dispensations', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'copayment_amount']);
        });
    }
};
