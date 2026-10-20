<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            if (!Schema::hasColumn('vital_signs', 'notes')) {
                $table->text('notes')->nullable()->after('overall_priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vital_signs', function (Blueprint $table) {
            if (Schema::hasColumn('vital_signs', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
