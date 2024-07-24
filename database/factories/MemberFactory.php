<?php

namespace JobMetric\Membership\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Membership\Models\Member;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personable_type' => null,
            'personable_id' => null,
            'memberable_type' => null,
            'memberable_id' => null,
            'collection' => null,
            'expired_at' => Carbon::now()->addDays(30),
        ];
    }

    /**
     * set personable
     *
     * @param string $personable_type
     * @param int $personable_id
     *
     * @return static
     */
    public function setPersonable(string $personable_type, int $personable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'personable_type' => $personable_type,
            'personable_id' => $personable_id
        ]);
    }

    /**
     * set memberable
     *
     * @param string $memberable_type
     * @param int $memberable_id
     *
     * @return static
     */
    public function setMemberable(string $memberable_type, int $memberable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'memberable_type' => $memberable_type,
            'memberable_id' => $memberable_id
        ]);
    }

    /**
     * set collection
     *
     * @param string $collection
     *
     * @return static
     */
    public function setCollection(string $collection): static
    {
        return $this->state(fn(array $attributes) => [
            'collection' => $collection
        ]);
    }

    /**
     * set expired_at
     *
     * @param Carbon $expired_at
     *
     * @return static
     */
    public function setExpiredAt(Carbon $expired_at): static
    {
        return $this->state(fn(array $attributes) => [
            'expired_at' => $expired_at
        ]);
    }
}
