<?php

namespace JobMetric\Membership\Exceptions;

use Exception;
use Throwable;

class MemberCollectionNotAllowedException extends Exception
{
    public function __construct(string $model, string $collection, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('membership::base.exceptions.member_collection_not_allowed', [
            'model' => $model,
            'collection' => $collection
        ]), $code, $previous);
    }
}
