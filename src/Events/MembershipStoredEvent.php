<?php

namespace JobMetric\Membership\Events;

use Illuminate\Database\Eloquent\Model;

class MembershipStoredEvent
{
    public Model $person;
    public Model $memberable;
    public string $collection;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $person, Model $memberable, string $collection)
    {
        $this->person = $person;
        $this->memberable = $memberable;
        $this->collection = $collection;
    }
}
