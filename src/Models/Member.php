<?php

namespace JobMetric\Membership\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * JobMetric\Membership\Models\Member
 *
 * @property int id
 * @property string user_id
 * @property string memberable_type
 * @property string memberable_id
 * @property string collection
 * @property string deleted_at
 * @property string created_at
 * @property string updated_at
 */
class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'user_id' => 'integer',
        'memberable_type' => 'string',
        'memberable_id' => 'integer',
        'collection' => 'string'
    ];

    public function getTable()
    {
        return config('membership.tables.member', parent::getTable());
    }

    /**
     * Set the scope of a query so that it only determines
     *
     * @param Builder $query
     * @param string $collection
     *
     * @return Builder
     */
    public function scopeOfUser(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Memberable relationship method to get the model that the member belongs to.
     *
     * @return MorphTo
     */
    public function memberable(): MorphTo
    {
        return $this->morphTo();
    }
}
