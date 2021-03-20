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
     * --date: Date before that log will be cleared. Format: Y-m-d
     * --day: Day before that log will be cleared. Format: /\d+/
     * 
     * @var string
     */
    protected $signature = 'clear:log {--date=} {--day=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear activity log before given days.';

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

        $this->cleanLogBeforeDays = $this->option('day') ?? $this->config['clean_log_before_days'];

        $date = $this->option('date') ?? Carbon::now()->subDays($this->cleanLogBeforeDays)->format('Y-m-d');

        try {
            DB::beginTransaction();

            $log = new ActivityLog();
            $records = $log->whereDate('activity_logs.created_at', '<=',  $date)
                        ->delete();

            $this->info("Deleted {$records} record(s) before {$this->cleanLogBeforeDays} day(s) from the activity log.");

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->error($th->getMessage());
        }

        return 0;
    }
}
