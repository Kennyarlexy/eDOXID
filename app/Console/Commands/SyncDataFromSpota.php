<?php

namespace App\Console\Commands;

use App\Helpers\SpotaHelper;
use Illuminate\Console\Command;

class SyncDataFromSpota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncdata:spota';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data dari SPOTA ke sistem ini';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncAllData();
    }
}
