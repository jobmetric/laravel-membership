<?php

namespace JobMetric\Membership;

use JobMetric\PackageCore\Exceptions\ConsoleKernelFileNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class MembershipServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     * @throws ConsoleKernelFileNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-membership')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->hasConsoleKernel()
            ->registerCommand(Commands\MemberRemove::class)
            ->registerClass('Membership', Membership::class);
    }
    /**
     * Register committed business outcome definitions alongside legacy events.
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
        if ($this->app->bound('EventRegistry')) {
            $registry = $this->app->make('EventRegistry');
            $registry->register(\JobMetric\Membership\Events\Membership\MembershipStoreCommittedEvent::class);
            $registry->register(\JobMetric\Membership\Events\Membership\MembershipDeleteCommittedEvent::class);
            $registry->register(\JobMetric\Membership\Events\Membership\MembershipRenewCommittedEvent::class);
            $registry->register(\JobMetric\Membership\Events\Membership\MembershipUpdateCommittedEvent::class);
            $registry->register(\JobMetric\Membership\Events\Membership\MembershipExpireCommittedEvent::class);
        }
    }
}
