<?php

namespace JobMetric\Membership\Events;

class MemberableResourceEvent
{
    /**
     * The memberable model instance.
     *
     * @var mixed
     */
    public mixed $memberable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $memberable
     */
    public function __construct(mixed $memberable)
    {
        $this->memberable = $memberable;
        $this->resource = null;
    }
}
