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
}
