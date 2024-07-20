<?php

namespace JobMetric\Membership;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Membership\Events\MembershipForgetEvent;
use JobMetric\Membership\Events\MembershipStoredEvent;
use JobMetric\Membership\Exceptions\MemberCollectionNotAllowedException;
use JobMetric\Membership\Exceptions\MemberCollectionTypeNotMatchException;
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
     *
     * @return static
     * @throws Throwable
     */
    public function memberIt(Model $person, string $collection): static
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        if ($allowMemberCollection[$collection] == 'single') {
            if ($this->member()->where([
                'collection' => $collection
            ])->exists()) {
                return $this;
            }
        } elseif ($allowMemberCollection[$collection] == 'multiple') {
            if ($this->member()->where([
                'personable_type' => get_class($person),
                'personable_id' => $person->getKey(),
                'collection' => $collection,
            ])->exists()) {
                return $this;
            }
        } else {
            throw new MemberCollectionTypeNotMatchException(self::class, $collection);
        }

        $this->member()->create([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ]);

        event(new MembershipStoredEvent($person, $this, $collection));

        return $this;
    }

    /**
     * forget member
     *
     * @param Model $person
     * @param string $collection
     *
     * @return static
     * @throws Throwable
     */
    public function memberForget(Model $person, string $collection): static
    {
        if (!in_array('JobMetric\Membership\CanMember', class_uses($person))) {
            throw new TraitCanMemberNotFoundInModelException(get_class($person));
        }

        $allowMemberCollection = $this->allowMemberCollection();

        if (!in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        $this->member()->where([
            'personable_type' => get_class($person),
            'personable_id' => $person->getKey(),
            'collection' => $collection,
        ])->delete();

        event(new MembershipForgetEvent($person, $this, $collection));

        return $this;
    }

    /**
     * check member
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
        ])->exists();
    }

    /**
     * get person
     *
     * @param string|null $collection
     *
     * @return AnonymousResourceCollection|null
     * @throws Throwable
     */
    public function getPerson(string $collection = null): ?AnonymousResourceCollection
    {
        $allowMemberCollection = $this->allowMemberCollection();

        if ($collection && !in_array($collection, array_keys($allowMemberCollection))) {
            throw new MemberCollectionNotAllowedException(self::class, $collection);
        }

        $members = $this->members()->get();

        if ($collection) {
            $members = $members->where('collection', $collection);
        }

        if ($members) {
            return MemberResource::collection($members);
        }

        return null;
    }
}
