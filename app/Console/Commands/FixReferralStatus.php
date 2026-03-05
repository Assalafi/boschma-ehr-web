<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encounter;

class FixReferralStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referrals:fix-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix existing referral records to have proper outcome field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing existing referral records...');
        
        // Update all encounters with STATUS_REFERRED to have outcome = 'Referred'
        $updated = DB::table('encounters')
            ->where('status', Encounter::STATUS_REFERRED)
            ->where(function($query) {
                $query->whereNull('outcome')
                      ->orWhere('outcome', '!=', 'Referred');
            })
            ->update(['outcome' => 'Referred']);

        $this->info("✅ Updated {$updated} existing referral records to have outcome = 'Referred'");
        
        // Show summary
        $totalReferred = DB::table('encounters')
            ->where('status', Encounter::STATUS_REFERRED)
            ->count();
            
        $withOutcome = DB::table('encounters')
            ->where('status', Encounter::STATUS_REFERRED)
            ->where('outcome', 'Referred')
            ->count();
        
        $this->info("📊 Summary:");
        $this->info("   Total referred encounters: {$totalReferred}");
        $this->info("   With correct outcome: {$withOutcome}");
        
        // Check for missing service_referrals
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
            $this->warn("⚠️  Warning: {$withoutReferral} encounters have STATUS_REFERRED but no service_referral record");
        } else {
            $this->info("✅ All referred encounters have corresponding service_referral records");
        }
        
        $this->info('✅ Referral status fix completed!');
        
        return 0;
    }
}
