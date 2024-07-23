<?php

namespace JobMetric\Membership\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MembershipRenewEvent
{
    public Model $person;
    public Model $memberable;
    public string $collection;
    public Carbon $expired_at;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $person, Model $memberable, string $collection, Carbon $expired_at)
    {
        $this->person = $person;
        $this->memberable = $memberable;
        $this->collection = $collection;
        $this->expired_at = $expired_at;
    }
}
