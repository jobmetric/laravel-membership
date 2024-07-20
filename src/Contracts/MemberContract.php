<?php

namespace JobMetric\Membership\Contracts;

interface MemberContract
{
    /**
     * allow the member collection.
     *
     * @return array
     */
    public function allowMemberCollection(): array;
}
