<?php

namespace JobMetric\Membership\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Membership\Membership
 */
class Membership extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Membership';
    }
}
