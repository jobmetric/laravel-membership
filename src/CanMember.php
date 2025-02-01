<?php

namespace JobMetric\Membership;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Membership\Events\MembershipForgetEvent;
use JobMetric\Membership\Events\MembershipRenewEvent;
use JobMetric\Membership\Events\MembershipStoredEvent;
use JobMetric\Membership\Events\MembershipUpdateExpiredAtEvent;
use JobMetric\Membership\Exceptions\MemberCollectionNotAllowedException;
use JobMetric\Membership\Exceptions\MemberCollectionTypeNotMatchException;
use JobMetric\Membership\Exceptions\MemberExpiredAtIsPastException;
use JobMetric\Membership\Exceptions\TraitHasMemberNotFoundInModelException;
use JobMetric\Membership\Http\Resources\MemberResource;
use JobMetric\Membership\Models\Member;
use JobMetric\Membership\Models\Member as MemberModel;
use Throwable;

/**
 * Trait CanMember
 *
 * @package JobMetric\Membership
 *
 * @property MemberModel member
 * @property MemberModel[] members
 *
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait CanMember
{
    /**
     * Person has one relationship
     *
     * @return MorphOne
     */
    public function person(): MorphOne
    {
        return $this->morphOne(MemberModel::class, 'personable');
    }

    /**
     * Person has many relationships
     *
     * @return MorphMany
     */
    public function persons(): MorphMany
    {
        return $this->morphMany(MemberModel::class, 'personable');
    }

    /**
     * store person
     *
     * @param Model $memberable
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return array
     * @throws Throwable
     */
    public function storePerson(Model $memberable, string $collection, Carbon $expired_at = null): array
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        if ($expired_at && $expired_at->isPast()) {
            throw new MemberExpiredAtIsPastException($expired_at);
        }

        $allowMemberCollection = $memberable->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
        }

        if ($allowMemberCollection[$collection] == 'single') {
            if (Member::query()->where([
                'memberable_type' => get_class($memberable),
                'memberable_id' => $memberable->getKey(),
                'collection' => $collection
            ])->where(function ($q) {
                $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
            })->exists()) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('membership::base.validation.member_collection_exists', [
                            'collection' => $collection
                        ]),
                    ],
                    'status' => 400,
                ];
            }
        } elseif ($allowMemberCollection[$collection] == 'multiple') {
            if (Member::query()->where([
                'personable_type' => self::class,
                'personable_id' => $this->getKey(),
                'memberable_type' => get_class($memberable),
                'memberable_id' => $memberable->getKey(),
                'collection' => $collection,
            ])->where(function ($q) {
                $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
            })->exists()) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('membership::base.validation.member_collection_exists', [
                            'collection' => $collection
                        ]),
                    ],
                    'status' => 400,
                ];
            }
        } else {
            throw new MemberCollectionTypeNotMatchException(get_class($memberable), $collection);
        }

        $member = MemberModel::create([
            'personable_type' => self::class,
            'personable_id' => $this->getKey(),
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
            'expired_at' => $expired_at,
        ]);

        event(new MembershipStoredEvent($this, $memberable, $collection, $expired_at));

        return [
            'ok' => true,
            'message' => trans('membership::base.messages.created'),
            'data' => MemberResource::make($member),
            'status' => 201
        ];
    }

    /**
     * forget person
     *
     * @param Model $memberable
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function forgetPerson(Model $memberable, string $collection): array
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        $allowMemberCollection = $memberable->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
        }

        $member = MemberModel::query()->where([
            'personable_type' => self::class,
            'personable_id' => $this->getKey(),
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ])->first();

        if (!$member) {
            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => [
                    trans('membership::base.validation.member_collection_not_found', [
                        'collection' => $collection
                    ]),
                ],
                'status' => 404,
            ];
        }

        $data = MemberResource::make($member);

        MemberModel::query()->where([
            'personable_type' => self::class,
            'personable_id' => $this->getKey(),
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ])->delete();

        event(new MembershipForgetEvent($this, $memberable, $collection));

        return [
            'ok' => true,
            'message' => trans('membership::base.messages.deleted'),
            'data' => $data,
            'status' => 200
        ];
    }

    /**
     * has person
     *
     * @param Model $memberable
     * @param string $collection
     *
     * @return bool
     * @throws Throwable
     */
    public function hasPerson(Model $memberable, string $collection): bool
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        $allowMemberCollection = $memberable->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
        }

        return $this->person()->where([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ])->where(function ($q) {
            $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
        })->exists();
    }

    /**
     * renew person
     *
     * @param Model $memberable
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return bool
     * @throws Throwable
     */
    public function renewPerson(Model $memberable, string $collection, Carbon $expired_at = null): bool
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        if ($expired_at && $expired_at->isPast()) {
            throw new MemberExpiredAtIsPastException($expired_at);
        }

        $allowMemberCollection = $memberable->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
        }

        if (!$this->person()->where([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ])->exists()) {
            return false;
        }

        $this->person()->updateOrInsert([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ], [
            'expired_at' => $expired_at,
        ]);

        event(new MembershipRenewEvent($this, $memberable, $collection, $expired_at));

        return true;
    }

    /**
     * update expired at person
     *
     * @param Model $memberable
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return bool
     * @throws Throwable
     */
    public function updateExpiredAtPerson(Model $memberable, string $collection, Carbon $expired_at = null): bool
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        $allowMemberCollection = $memberable->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
        }

        if (!$this->person()->where([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ])->exists()) {
            return false;
        }

        $this->person()->updateOrInsert([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ], [
            'expired_at' => $expired_at,
        ]);

        event(new MembershipUpdateExpiredAtEvent($this, $memberable, $collection, $expired_at));

        return true;
    }

    /**
     * get member
     *
     * @param Model|null $memberable
     * @param string|null $collection
     * @param bool $is_expired
     *
     * @return AnonymousResourceCollection
     * @throws Throwable
     */
    public function getMember(Model $memberable = null, string $collection = null, bool $is_expired = false): AnonymousResourceCollection
    {
        if ($memberable && !in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
        }

        if ($memberable) {
            $allowMemberCollection = $memberable->allowMemberCollection();

            if ($collection && !in_array($collection, array_keys($allowMemberCollection))) {
                throw new MemberCollectionNotAllowedException(get_class($memberable), $collection);
            }
        }

        $members = $this->person()->where(function ($q) use ($memberable, $collection, $is_expired) {
            if ($memberable) {
                $q->where('memberable_type', get_class($memberable));
                $q->where('memberable_id', $memberable->getKey());
            }

            if ($collection) {
                $q->where('collection', $collection);
            }

            if ($is_expired) {
                $q->where('expired_at', '<', Carbon::now());
            } else {
                $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
            }
        })->get();

        return MemberResource::collection($members);
    }
}
