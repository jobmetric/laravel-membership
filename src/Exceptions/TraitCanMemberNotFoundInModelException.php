<?php

namespace JobMetric\Membership\Exceptions;

use Exception;
use Throwable;

class TraitCanMemberNotFoundInModelException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('membership::base.exceptions.trait_can_member_not_found_in_model', [
            'model' => $model
        ]), $code, $previous);
    }
}
