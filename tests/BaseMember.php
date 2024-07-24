<?php

namespace JobMetric\Membership\Tests;

use App\Models\Order;
use App\Models\User;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class BaseMember extends BaseTestCase
{
    public function addOrder(): Order
    {
        return Order::factory()->create();
    }

    public function addUser(): User
    {
        return User::factory()->create();
    }
}
