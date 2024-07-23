<?php

namespace JobMetric\Membership\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JobMetric\Membership\Events\MemberableResourceEvent;
use JobMetric\Membership\Events\PersonableResourceEvent;
use JobMetric\PackageCore\HasDynamicRelations;

/**
 * JobMetric\Membership\Models\Member
 *
 * @property string personable_type
 * @property int personable_id
 * @property string memberable_type
 * @property int memberable_id
 * @property string collection
 * @property Carbon expired_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Member extends Pivot
{
    use HasFactory, HasDynamicRelations;

    protected $fillable = [
        'personable_type',
        'personable_id',
        'memberable_type',
        'memberable_id',
        'collection',
        'expires_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personable_type' => 'string',
        'personable_id' => 'integer',
        'memberable_type' => 'string',
        'memberable_id' => 'integer',
        'collection' => 'string',
        'expires_at' => 'datetime'
    ];

    public function getTable()
    {
        return config('membership.tables.member', parent::getTable());
    }

    /**
     * Personable relationship method.
     *
     * @return MorphTo
     */
    public function personable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Memberable relationship method.
     *
     * @return MorphTo
     */
    public function memberable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include categories of a given collection.
     *
     * @param Builder $query
     * @param string $collection
     *
     * @return Builder
     */
    public function scopeOfCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Set the scope of a query to include only those that have expired.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Set the scope of a query to include only those that have not expired.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>=', now());
    }

    /**
     * Get the personable resource attribute.
     *
     * @return mixed|null
     */
    public function getPersonableResourceAttribute(): mixed
    {
        $event = new PersonableResourceEvent($this->personable);
        event($event);

        return $event->resource;
    }

    /**
     * Get the memberable resource attribute.
     *
     * @return mixed|null
     */
    public function getMemberableResourceAttribute(): mixed
    {
        $event = new MemberableResourceEvent($this->memberable);
        event($event);

        return $event->resource;
    }
}
