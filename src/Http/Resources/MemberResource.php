<?php

namespace JobMetric\Membership\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed personable_type
 * @property mixed personable_id
 * @property mixed memberable_type
 * @property mixed memberable_id
 * @property mixed collection
 * @property mixed expired_at
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed personable_resource
 * @property mixed memberable_resource
 */
class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'personable_type' => $this->personable_type,
            'personable_id' => $this->personable_id,
            'memberable_type' => $this->memberable_type,
            'memberable_id' => $this->memberable_id,
            'collection' => $this->collection,
            'expired_at' => $this->expired_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'personable' => $this->personable_resource,
            'memberable' => $this->memberable_resource,
        ];
    }
}
