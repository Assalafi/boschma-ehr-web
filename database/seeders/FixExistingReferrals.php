<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Encounter;

class FixExistingReferrals extends Seeder
{
    /**
     * Fix existing referrals to have proper outcome field
     * This ensures they don't appear in Completed tab after the referral flow fix
     */
    public function run()
    {
        // Update all encounters with STATUS_REFERRED to have outcome = 'Referred'
        $updated = DB::table('encounters')
            ->where('status', Encounter::STATUS_REFERRED)
            ->where(function($query) {
                $query->whereNull('outcome')
                      ->orWhere('outcome', '!=', 'Referred');
            })
            ->update(['outcome' => 'Referred']);

        $this->command->info("Updated {$updated} existing referral records to have outcome = 'Referred'");
        
        // Also verify that service_referrals exist for these encounters
        $withoutReferral = DB::table('encounters')
            ->where('status', Encounter::STATUS_REFERRED)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('service_referrals')
                      ->whereRaw('service_referrals.encounter_id = encounters.id')
                      ->where('referral_type', 'patient')
                      ->whereNull('service_item_id');
            })
            ->count();

        if ($withoutReferral > 0) {
            $this->command->warn("Warning: {$withoutReferral} encounters have STATUS_REFERRED but no service_referral record");
        }
    }
}
