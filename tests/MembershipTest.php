<?php

namespace JobMetric\Membership\Tests;

use JobMetric\Membership\Facades\Membership;
use JobMetric\Membership\Http\Resources\MemberResource;
use Throwable;

class MembershipTest extends BaseMember
{
    /**
     * @throws Throwable
     */
    public function test_all(): void
    {
        $user = $this->addUser();
        $order = $this->addOrder();

        $order->storeMember($user, 'owner');

        // Get the members
        $getMembers = Membership::all();

        $this->assertCount(1, $getMembers);

        $getMembers->each(function ($member) {
            $this->assertInstanceOf(MemberResource::class, $member);
        });
    }

    /**
     * @throws Throwable
     */
    public function test_paginate(): void
    {
        $user = $this->addUser();
        $order = $this->addOrder();

        $order->storeMember($user, 'owner');

        // Paginate the members
        $paginateMembers = Membership::paginate();

        $this->assertCount(1, $paginateMembers);

        $paginateMembers->each(function ($member) {
            $this->assertInstanceOf(MemberResource::class, $member);
        });

        $this->assertIsInt($paginateMembers->total());
        $this->assertIsInt($paginateMembers->perPage());
        $this->assertIsInt($paginateMembers->currentPage());
        $this->assertIsInt($paginateMembers->lastPage());
        $this->assertIsArray($paginateMembers->items());
    }

    /**
     * @throws Throwable
     */
    public function test_remove_expired_member(): void
    {
        $user = $this->addUser();
        $order = $this->addOrder();

        $order->storeMember($user, 'owner');

        $order->updateExpiredAtMember($user, 'owner', now()->subDays(30));

        // Remove the expired members
        $remove = Membership::removeExpiredMember();

        $this->assertTrue($remove);

        $this->assertDatabaseCount('members', 0);

        // Remove the expired members again
        $remove = Membership::removeExpiredMember();

        $this->assertFalse($remove);
    }
}
