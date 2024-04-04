<?php

namespace JobMetric\Membership\Factories;

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
            'user_id' => null,
            'memberable_type' => null,
            'memberable_id' => null,
            'collection' => null,
        ];
    }

    /**
     * set user id
     *
     * @param int $user_id
     *
     * @return static
     */
    public function setUserId(int $user_id): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user_id
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
}
