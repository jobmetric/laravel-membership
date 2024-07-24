<?php

namespace JobMetric\Membership\Events;

use JobMetric\Membership\Models\Member;

class MembershipRemoveExpiredEvent
{
    public Member $member;

    /**
     * Create a new event instance.
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }
}
