<?php

namespace JobMetric\Membership\Events;

class PersonableResourceEvent
{
    /**
     * The personable model instance.
     *
     * @var mixed
     */
    public mixed $personable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $personable
     */
    public function __construct(mixed $personable)
    {
        $this->personable = $personable;
        $this->resource = null;
    }
}
