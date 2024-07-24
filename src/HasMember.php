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
use JobMetric\Membership\Exceptions\ModelMemberContractNotFoundException;
use JobMetric\Membership\Exceptions\TraitCanMemberNotFoundInModelException;
use JobMetric\Membership\Http\Resources\MemberResource;
use JobMetric\Membership\Models\Member as MemberModel;
use Throwable;

/**
 * Trait HasMember
 *
 * @package JobMetric\Membership
 *
 * @property MemberModel member
 * @property MemberModel[] members
 *
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 * @method allowMemberCollection()
 */
trait HasMember
{
    /**
     * boot has member trait
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasMember(): void
    {
        if (!in_array('JobMetric\Membership\Contracts\MemberContract', class_implements(self::class))) {
            throw new ModelMemberContractNotFoundException(self::class);
        }
    }

    /**
     * Member has one relationship
     *
     * @return MorphOne
     */
    public function member(): MorphOne
    {
        return $this->morphOne(MemberModel::class, 'memberable');
    }

    /**
     * Member has many relationships
     *
     * @return MorphMany
     */
    public function members(): MorphMany
    {
        return $this->morphMany(MemberModel::class, 'memberable');
    }

    /**
     * store member
     *
     * @param Model $person
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return array
     * @throws Throwable
     */
    public function storeMember(Model $person, string $collection, Carbon $expired_at = null): array
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        if ($expired_at && $expired_at->isPast()) {
            throw new MemberExpiredAtIsPastException($expired_at);
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        if ($allowMemberCollection[$collection] == 'single') {
            if ($this->member()->where([
                'collection' => $collection,
            ])->where(function ($q) {
                $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
            })->exists()) {
                return [
                    'ok' => false,
                    'message' => trans('membership::base.validation.errors'),
                    'errors' => [
                        trans('membership::base.validation.member_collection_exists', [
                            'collection' => $collection
                        ]),
                    ],
                    'status' => 400,
                ];
            }
        } elseif ($allowMemberCollection[$collection] == 'multiple') {
            if ($this->member()->where([
                'personable_type' => get_class($person),
                'personable_id' => $person->getKey(),
                'collection' => $collection,
            ])->where(function ($q) {
                $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
            })->exists()) {
                return [
                    'ok' => false,
                    'message' => trans('membership::base.validation.errors'),
                    'errors' => [
                        trans('membership::base.validation.member_collection_exists', [
                            'collection' => $collection
                        ]),
                    ],
                    'status' => 400,
                ];
            }
        } else {
            throw new MemberCollectionTypeNotMatchException(self::class, $collection);
        }

        $member = MemberModel::create([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'memberable_type' => self::class,
            'memberable_id' => $this->getKey(),
            'collection' => $collection,
            'expired_at' => $expired_at,
        ]);

        event(new MembershipStoredEvent($person, $this, $collection, $expired_at));

        return [
            'ok' => true,
            'message' => trans('membership::base.messages.created'),
            'data' => MemberResource::make($member),
            'status' => 201
        ];
    }

    /**
     * forget member
     *
     * @param Model $person
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function forgetMember(Model $person, string $collection): array
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        $member = MemberModel::query()->where([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'memberable_type' => self::class,
            'memberable_id' => $this->getKey(),
            'collection' => $collection,
        ])->first();

        if (!$member) {
            return [
                'ok' => false,
                'message' => trans('membership::base.validation.errors'),
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
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'memberable_type' => self::class,
            'memberable_id' => $this->getKey(),
            'collection' => $collection,
        ])->delete();

        event(new MembershipForgetEvent($person, $this, $collection));

        return [
            'ok' => true,
            'message' => trans('membership::base.messages.deleted'),
            'data' => $data,
            'status' => 200
        ];
    }

    /**
     * has member
     *
     * @param Model $person
     * @param string $collection
     *
     * @return bool
     * @throws Throwable
     */
    public function hasMember(Model $person, string $collection): bool
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        return $this->member()->where([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ])->where(function ($q) {
            $q->where('expired_at', '>', Carbon::now())->orWhereNull('expired_at');
        })->exists();
    }

    /**
     * renew member
     *
     * @param Model $person
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return bool
     * @throws Throwable
     */
    public function renewMember(Model $person, string $collection, Carbon $expired_at = null): bool
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        if ($expired_at && $expired_at->isPast()) {
            throw new MemberExpiredAtIsPastException($expired_at);
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        if (!$this->member()->where([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ])->exists()) {
            return false;
        }

        $this->member()->updateOrInsert([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ], [
            'expired_at' => $expired_at,
        ]);

        event(new MembershipRenewEvent($person, $this, $collection, $expired_at));

        return true;
    }

    /**
     * update expired at member
     *
     * @param Model $person
     * @param string $collection
     * @param Carbon|null $expired_at
     *
     * @return bool
     * @throws Throwable
     */
    public function updateExpiredAtMember(Model $person, string $collection, Carbon $expired_at = null): bool
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        if (!$this->member()->where([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ])->exists()) {
            return false;
        }

        $this->member()->updateOrInsert([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ], [
            'expired_at' => $expired_at,
        ]);

        event(new MembershipUpdateExpiredAtEvent($person, $this, $collection, $expired_at));

        return true;
    }

    /**
     * get person
     *
     * @param string|null $collection
     * @param bool $is_expired
     *
     * @return AnonymousResourceCollection
     * @throws Throwable
     */
    public function getPerson(string $collection = null, bool $is_expired = false): AnonymousResourceCollection
    {
        $allowMemberCollection = $this->allowMemberCollection();

        if ($collection && !in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        $members = $this->members()->where(function ($q) use ($collection, $is_expired) {
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
