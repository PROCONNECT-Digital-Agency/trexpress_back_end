<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $name;
    protected        $row;
    protected        $exportClass;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $row, $exportClass)
    {
        $this->name        = $name;
        $this->row         = $row;
        $this->exportClass = $exportClass;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set("memory_limit", "2G");
        set_time_limit(86400);

        Excel::store(new $this->exportClass($this->row), $this->name, 'public');

//        FileDeleteJob::dispatch("storage/export/{$this->name}.xlsx")
//            ->delay(now()->addSeconds(1000));
    }
}
