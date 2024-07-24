<?php

namespace JobMetric\Membership;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel;

class ConsoleKernel extends Kernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('member:remove')->hourly();
    }
}
