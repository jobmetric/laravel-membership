<?php

namespace JobMetric\Membership\Exceptions;

use Carbon\Carbon;
use Exception;
use Throwable;

class MemberExpiredAtIsPastException extends Exception
{
    public function __construct(Carbon $expired_at, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('membership::base.exceptions.member_expired_at_is_past', [
            'expired_at' => $expired_at->format('Y-m-d H:i:s')
        ]), $code, $previous);
    }
}
