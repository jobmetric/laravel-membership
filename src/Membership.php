<?php

namespace JobMetric\Membership;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Membership\Events\MembershipRemoveExpiredEvent;
use JobMetric\Membership\Http\Resources\MemberResource;
use JobMetric\Membership\Models\Member;
use Spatie\QueryBuilder\QueryBuilder;

class Membership
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Setting instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the specified members.
     *
     * @param array $filter
     * @param array $with
     *
     * @return QueryBuilder
     */
    private function query(array $filter = [], array $with = []): QueryBuilder
    {
        $fields = [
            'personable_type',
            'personable_id',
            'memberable_type',
            'memberable_id',
            'collection',
            'expired_at',
            'created_at',
            'updated_at'
        ];

        $query = QueryBuilder::for(Member::class);

        $query->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort([
                '-created_at',
            ])
            ->where($filter);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * Paginate the specified members.
     *
     * @param array $filter
     * @param int $page_limit
     * @param array $with
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(array $filter = [], int $page_limit = 15, array $with = []): AnonymousResourceCollection
    {
        return MemberResource::collection(
            $this->query($filter, $with)->paginate($page_limit)
        );
    }

    /**
     * Get all the specified members.
     *
     * @param array $filter
     * @param array $with
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = []): AnonymousResourceCollection
    {
        return MemberResource::collection(
            $this->query($filter, $with)->get()
        );
    }

    /**
     * Remove Member has been expired.
     *
     * @return bool
     */
    public function removeExpiredMember(): bool
    {
        $member_expired = Member::query()->where('expired_at', '<', now())->get();

        if ($member_expired->count()) {
            $member_expired->each(function ($member) {
                /**
                 * @var Member $member
                 */
                Member::query()->where([
                    'personable_type' => $member->personable_type,
                    'personable_id' => $member->personable_id,
                    'memberable_type' => $member->memberable_type,
                    'memberable_id' => $member->memberable_id,
                    'collection' => $member->collection,
                ])->delete();

                event(new MembershipRemoveExpiredEvent($member));
            });

            return true;
        }

        return false;

    }
}
