<?php

namespace App\Console\Commands;

use App\Models\Car;
use Illuminate\Console\Command;
use App\Models\Dealer;
use App\Models\CarQueue;
use App\Models\ScrappingReport;
use QL\QueryList;

/**
 * Class ScrapingMasterWWW
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class WWW extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'cars:WWW {dealer?} {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch cars from WWW';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = time();
        if ($this->argument('dealer')) {
            $type = 'manual';
            $dealers = Dealer::where('source', 'WWW')->where('id', $this->argument('dealer'))->get();
        } else {
            $type = 'cron job';
            $dealers = Dealer::where(['active' => true, 'source' => 'WWW'])->orderBy('type', 'asc')->get();
        }

        $count = $dealers->count();
        foreach ($dealers as $key => $dealer) {

        dispatch(new \App\Jobs\CopyCarsFromGroupedToMaster($dealer,$type))->onQueue('copy_cars_to_master');
        }

        $content = date('Y-m-d h:i:sa') . " : $count total of dealers today WWW" . "\n";
        \Log::info($content);

    }
}