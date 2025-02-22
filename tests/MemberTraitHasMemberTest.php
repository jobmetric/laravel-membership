<?php

namespace JobMetric\Membership\Tests;

use App\Models\Order;
use App\Models\User;
use JobMetric\Membership\Exceptions\MemberExpiredAtIsPastException;
use JobMetric\Membership\Http\Resources\MemberResource;
use Throwable;

class MemberTraitHasMemberTest extends BaseMember
{
    public function test_check_has_member_trait()
    {
        $order = new Order;
        $this->assertIsArray($order->allowMemberCollection());
    }

    public function test_member_trait_relationship()
    {
        $order = new Order;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $order->member());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $order->members());
    }

    /**
     * @throws Throwable
     */
    public function test_store()
    {
        $order = $this->addOrder();
        $user_1 = $this->addUser();
        $user_2 = $this->addUser();
        $user_3 = $this->addUser();

        // store member with null expired_at
        $member_1 = $order->storeMember($user_1, 'owner');

        $this->assertIsArray($member_1);
        $this->assertTrue($member_1['ok']);
        $this->assertEquals($member_1['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $member_1['data']);
        $this->assertEquals(201, $member_1['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user_1->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'owner',
            'expired_at' => null
        ]);

        // store member duplicate single collection
        $member_2 = $order->storeMember($user_1, 'owner');

        $this->assertIsArray($member_2);
        $this->assertFalse($member_2['ok']);
        $this->assertEquals($member_2['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(400, $member_2['status']);

        $this->assertDatabaseCount('members', 1);

        // check error exist add user_2 to the owner single collection
        $member_3 = $order->storeMember($user_2, 'owner');

        $this->assertIsArray($member_3);
        $this->assertFalse($member_3['ok']);
        $this->assertEquals($member_3['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(400, $member_3['status']);

        // store member to the multiple collection
        $member_4 = $order->storeMember($user_1, 'members');

        $this->assertIsArray($member_4);
        $this->assertTrue($member_4['ok']);
        $this->assertEquals($member_4['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $member_4['data']);
        $this->assertEquals(201, $member_4['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user_1->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'members',
            'expired_at' => null
        ]);

        // store another user to the multiple collection
        $member_5 = $order->storeMember($user_2, 'members');

        $this->assertIsArray($member_5);
        $this->assertTrue($member_5['ok']);
        $this->assertEquals($member_5['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $member_5['data']);
        $this->assertEquals(201, $member_5['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user_2->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'members',
            'expired_at' => null
        ]);

        // store another user to the multiple collection with expired_at
        $time = now()->addDays(30);
        $member_6 = $order->storeMember($user_3, 'members', $time);

        $this->assertIsArray($member_6);
        $this->assertTrue($member_6['ok']);
        $this->assertEquals($member_6['message'], trans('membership::base.messages.created'));
        $this->assertInstanceOf(MemberResource::class, $member_6['data']);
        $this->assertEquals(201, $member_6['status']);

        $this->assertDatabaseHas('members', [
            'personable_type' => User::class,
            'personable_id' => $user_3->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
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

        $order->storeMember($user, 'owner');

        $memberForget = $order->forgetMember($user, 'owner');

        $this->assertIsArray($memberForget);
        $this->assertTrue($memberForget['ok']);
        $this->assertEquals($memberForget['message'], trans('membership::base.messages.deleted'));
        $this->assertEquals(200, $memberForget['status']);

        $this->assertDatabaseMissing('members', [
            'personable_type' => User::class,
            'personable_id' => $user->id,
            'memberable_type' => Order::class,
            'memberable_id' => $order->id,
            'collection' => 'owner',
            'expired_at' => null
        ]);

        // forget member not found
        $memberForget = $order->forgetMember($user, 'owner');

        $this->assertIsArray($memberForget);
        $this->assertFalse($memberForget['ok']);
        $this->assertEquals($memberForget['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(404, $memberForget['status']);
    }

    /**
     * @throws Throwable
     */
    public function test_has(): void
    {
        $order = $this->addOrder();
        $user_1 = $this->addUser();

        $order->storeMember($user_1, 'owner');

        $memberHas = $order->hasMember($user_1, 'owner');

        $this->assertIsBool($memberHas);
        $this->assertTrue($memberHas);

        $user_2 = $this->addUser();

        $memberHas = $order->hasMember($user_2, 'owner');

        $this->assertIsBool($memberHas);
        $this->assertFalse($memberHas);
    }

    /**
     * @throws Throwable
     */
    public function test_renew(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $order->storeMember($user, 'owner');

        $time = now()->addDays(30);
        $memberRenew = $order->renewMember($user, 'owner', $time);

        $this->assertIsBool($memberRenew);
        $this->assertTrue($memberRenew);

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
            $order->renewMember($user, 'owner', $time);
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

        $order->storeMember($user, 'owner');

        $time = now()->addDays(30);
        $memberUpdate = $order->updateExpiredAtMember($user, 'owner', $time);

        $this->assertIsBool($memberUpdate);
        $this->assertTrue($memberUpdate);

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

        $memberUpdate = $order->updateExpiredAtMember($user, 'owner', $time);

        $this->assertIsBool($memberUpdate);
        $this->assertTrue($memberUpdate);
    }

    /**
     * @throws Throwable
     */
    public function test_get_person(): void
    {
        $order = $this->addOrder();
        $user = $this->addUser();

        $order->storeMember($user, 'owner');

        $persons = $order->getPerson('owner');

        $this->assertCount(1, $persons);

        $persons->each(function ($person) {
            $this->assertInstanceOf(MemberResource::class, $person);
        });
    }
}
