<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('service_referrals')) {
            return;
        }

        Schema::table('service_referrals', function (Blueprint $table) {
            if (!Schema::hasColumn('service_referrals', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('service_referrals', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('service_referrals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('service_referrals', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('service_referrals', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('service_referrals')) {
            return;
        }

        Schema::table('service_referrals', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
            ]);
        });
    }
};
