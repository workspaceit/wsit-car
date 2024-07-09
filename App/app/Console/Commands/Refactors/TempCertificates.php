<?php

namespace App\Console\Commands\Refactors;

use App\Models\TempCertificate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class TempCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refactor:temp-certificates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refactor temp certificates users data.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TempCertificate::withTrashed()->chunk(100, function($tempCertificates){
            foreach ($tempCertificates as $tempCertificate) {
                try {
                    $user = null;
                    if (!empty($tempCertificate->customer()->withTrashed()->exists())) {
                        $customer = $tempCertificate->customer()->withTrashed()->first();
                        $user = $customer->user()->first();
                    }

                    if (empty($user)) {
                        $user = User::where("type", User::TYPE_DEALER)
                            ->where('dealers', 'like', '%"' . $tempCertificate->dealer_id . '"%')
                            ->firstOrFail();
                    }
                    $tempCertificate->update([
                        'user_id' => $user->id,
                        'username' => $user->username
                    ]);

                    Log::info("User added to temp certificate document. Doc Id : ". $tempCertificate->id);
                }catch (Throwable $throwable){
                    Log::error($throwable->getMessage());
                    Log::error("Failed to refactor temp certificate document. Doc Id : ". $tempCertificate->id);
                }
            }
        });
    }
}