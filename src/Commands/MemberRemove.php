<?php

namespace JobMetric\Membership\Commands;

use Illuminate\Console\Command;
use JobMetric\Membership\Facades\Membership;
use JobMetric\PackageCore\Commands\ConsoleTools;

class MemberRemove extends Command
{
    use ConsoleTools;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove member expire time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $removeExpired = Membership::removeExpiredMember();

        if ($removeExpired) {
            $this->message('Member has been <options=bold>removed</> successfully.', 'success');
            return 0;
        }

        $this->message('No Member expire found.', 'error');
        return 1;

    }
}
