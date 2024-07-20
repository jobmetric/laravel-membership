<?php

namespace JobMetric\Membership;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JobMetric\Membership\Events\MembershipStoredEvent;
use JobMetric\Membership\Exceptions\MemberCollectionNotAllowedException;
use JobMetric\Membership\Exceptions\MemberCollectionTypeNotMatchException;
use JobMetric\Membership\Exceptions\TraitHasMemberNotFoundInModelException;
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
     *
     * @return static
     * @throws Throwable
     */
    public function memberIt(Model $memberable, string $collection): static
    {
        if (!in_array('JobMetric\Membership\HasMember', class_uses($memberable))) {
            throw new TraitHasMemberNotFoundInModelException(get_class($memberable));
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
            ])->exists()) {
                return $this;
            }
        } elseif ($allowMemberCollection[$collection] == 'multiple') {
            if (Member::query()->where([
                'personable_type' => self::class,
                'personable_id' => $this->getKey(),
                'memberable_type' => get_class($memberable),
                'memberable_id' => $memberable->getKey(),
                'collection' => $collection,
            ])->exists()) {
                return $this;
            }
        } else {
            throw new MemberCollectionTypeNotMatchException(get_class($memberable), $collection);
        }

        $this->person()->create([
            'memberable_type' => get_class($memberable),
            'memberable_id' => $memberable->getKey(),
            'collection' => $collection,
        ]);

        event(new MembershipStoredEvent($this, $memberable, $collection));

        return $this;
    }
}
