<?php

namespace JobMetric\Membership\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * JobMetric\Membership\Models\Member
 *
 * @property string personable_type
 * @property int personable_id
 * @property string memberable_type
 * @property int memberable_id
 * @property string collection
 * @property string created_at
 */
class Member extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'personable_type',
        'personable_id',
        'memberable_type',
        'memberable_id',
        'collection'
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
        'collection' => 'string'
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
        return $this->morphTo(__FUNCTION__, 'personable_type', 'personable_id');
    }

    /**
     * Memberable relationship method.
     *
     * @return MorphTo
     */
    public function memberable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'memberable_type', 'memberable_id');
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
}
