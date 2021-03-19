<?php

namespace Amsrafid\ActivityLog\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Amsrafid\ActivityLog\Models\ActivityLog;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:log {--D|date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear activity log by given time.';

    /**
     * The log configuration bag.
     *
     * @var array
     */
    protected $config = null;

    /**
     * Clean before given days
     * 
     * @var int
     */
    private $cleanLogBeforeDays = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = config('activitylog');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! isset($this->config['clean_log_before_days'])
            || $this->config['clean_log_before_days'] < 1) {
            return;
        }
        
        $this->comment('Log cleaning in process...');

        $this->cleanLogBeforeDays = $this->config['clean_log_before_days'];

        $date = $this->option('date') ?? Carbon::now()->subDays($this->cleanLogBeforeDays)->format('Y-m-d');

        try {
            DB::beginTransaction();

            $log = new ActivityLog();
            $log->whereDate('activity_logs.created_at', '<',  $date)
                ->delete();

            $this->info("Deleted records before {$this->cleanLogBeforeDays} day(s) from the activity log.");

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->error($th->getMessage());
        }

        return 0;
    }
}
