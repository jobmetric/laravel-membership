<?php

namespace JobMetric\Membership\Tests;

use App\Models\Order;
use App\Models\User;
use JobMetric\Membership\Exceptions\MemberExpiredAtIsPastException;
use JobMetric\Membership\Http\Resources\MemberResource;
use Throwable;

class MemberTraitCanMemberTest extends BaseMember
{
    public function test_person_trait_relationship()
    {
        $user = new User();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $user->person());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $user->persons());
    }

    /**
     * @throws Throwable
     */
    public function test_store()
    {
        $user = $this->addUser();
        $user_2 = $this->addUser();
        $order_1 = $this->addOrder();
        $order_2 = $this->addOrder();
        $order_3 = $this->addOrder();

        // store person with null expired_at
        $person_1 = $user->storePerson($order_1, 'owner');

        $this->assertIsArray($person_1);
        $this->assertTrue($person_1['ok']);
        $this->assertEquals($person_1['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $person_1['data']);
        $this->assertEquals(201, $person_1['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order_1->id,
            'collection' => 'owner',
            'expired_at' => null
        ]);

        // store person duplicate single collection
        $person_2 = $user->storePerson($order_1, 'owner');

        $this->assertIsArray($person_2);
        $this->assertFalse($person_2['ok']);
        $this->assertEquals($person_2['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(400, $person_2['status']);

        $this->assertDatabaseCount('members', 1);

        // check error exist add user_2 to the owner single collection
        $person_3 = $user_2->storePerson($order_1, 'owner');

        $this->assertIsArray($person_3);
        $this->assertFalse($person_3['ok']);
        $this->assertEquals($person_3['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(400, $person_3['status']);

        // store person to the multiple collection
        $person_4 = $user->storePerson($order_1, 'members');

        $this->assertIsArray($person_4);
        $this->assertTrue($person_4['ok']);
        $this->assertEquals($person_4['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $person_4['data']);
        $this->assertEquals(201, $person_4['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order_1->id,
            'collection' => 'members',
            'expired_at' => null
        ]);

        // store another user to the multiple collection
        $person_5 = $user_2->storePerson($order_1, 'members');

        $this->assertIsArray($person_5);
        $this->assertTrue($person_5['ok']);
        $this->assertEquals($person_5['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $person_5['data']);
        $this->assertEquals(201, $person_5['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order_1->id,
            'collection' => 'members',
            'expired_at' => null
        ]);

        // store another user to the multiple collection with expired_at
        $time = now()->addDays(30);
        $person_6 = $user->storePerson($order_3, 'members', $time);

        $this->assertIsArray($person_6);
        $this->assertTrue($person_6['ok']);
        $this->assertEquals($person_6['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $person_6['data']);
        $this->assertEquals(201, $person_6['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order_3->id,
            'collection' => 'members',
            'expired_at' => $time
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_forget(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $user->storePerson($order, 'owner');

        $personForget = $user->forgetPerson($order, 'owner');

        $this->assertIsArray($personForget);
        $this->assertTrue($personForget['ok']);
        $this->assertEquals($personForget['message'], trans('membership::base.messages.deleted'));
        $this->assertEquals(200, $personForget['status']);

        $this->assertDatabaseMissing('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'owner',
            'expired_at' => null
        ]);

        // forget person not found
        $personForget = $user->forgetPerson($order, 'owner');

        $this->assertIsArray($personForget);
        $this->assertFalse($personForget['ok']);
        $this->assertEquals($personForget['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(404, $personForget['status']);
    }

    /**
     * @throws Throwable
     */
    public function test_has(): void
    {
        $user = $this->addUser();
        $order_1 = $this->addOrder();
        $order_2 = $this->addOrder();

        $user->storePerson($order_1, 'owner');

        $personHas = $user->hasPerson($order_1, 'owner');

        $this->assertIsBool($personHas);
        $this->assertTrue($personHas);

        $personHas = $user->hasPerson($order_2, 'owner');

        $this->assertIsBool($personHas);
        $this->assertFalse($personHas);
    }

    /**
     * @throws Throwable
     */
    public function test_renew(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $user->storePerson($order, 'owner');

        $time = now()->addDays(30);
        $personRenew = $user->renewPerson($order, 'owner', $time);

        $this->assertIsBool($personRenew);
        $this->assertTrue($personRenew);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'owner',
            'expired_at' => $time
        ]);

        // renew past expired_at
        $time = now()->subDays(30);

        try {
            $user->renewPerson($order, 'owner', $time);
        } catch (Throwable $e) {
            $this->assertInstanceOf(MemberExpiredAtIsPastException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_update_expired_at(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $user->storePerson($order, 'owner');

        $time = now()->addDays(30);
        $personUpdate = $user->updateExpiredAtPerson($order, 'owner', $time);

        $this->assertIsBool($personUpdate);
        $this->assertTrue($personUpdate);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'owner',
            'expired_at' => $time
        ]);

        // update expired_at past
        $time = now()->subDays(30);

        $personUpdate = $user->updateExpiredAtPerson($order, 'owner', $time);

        $this->assertIsBool($personUpdate);
        $this->assertTrue($personUpdate);
    }

    /**
     * @throws Throwable
     */
    public function test_get_member(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $user->storePerson($order, 'owner');

        $members = $user->getMember();

        $this->assertCount(1, $members);

        $members->each(function ($member) {
            $this->assertInstanceOf(MemberResource::class, $member);
        });
    }
}
