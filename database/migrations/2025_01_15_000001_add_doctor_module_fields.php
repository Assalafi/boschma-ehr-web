<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add outcome and follow_up_date to encounters table
        Schema::table('encounters', function (Blueprint $table) {
            if (!Schema::hasColumn('encounters', 'outcome')) {
                $table->string('outcome')->nullable()->after('status');
            }
            if (!Schema::hasColumn('encounters', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('outcome');
            }
        });

        // Add fields to investigations table
        Schema::table('investigations', function (Blueprint $table) {
            if (!Schema::hasColumn('investigations', 'type')) {
                $table->string('type')->nullable()->after('clinical_consultation_id');
            }
            if (!Schema::hasColumn('investigations', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('investigations', 'tests')) {
                $table->json('tests')->nullable()->after('category');
            }
            if (!Schema::hasColumn('investigations', 'notes')) {
                $table->text('notes')->nullable()->after('tests');
            }
            if (!Schema::hasColumn('investigations', 'requested_by')) {
                $table->foreignUuid('requested_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        // Add prescribed_by to prescriptions table
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'prescribed_by')) {
                $table->foreignUuid('prescribed_by')->nullable()->after('clinical_consultation_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            $table->dropColumn(['outcome', 'follow_up_date']);
        });

        Schema::table('investigations', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['type', 'category', 'tests', 'notes', 'requested_by']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['prescribed_by']);
            $table->dropColumn('prescribed_by');
        });
    }
};
