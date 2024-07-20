<?php

namespace JobMetric\Membership\Exceptions;

use Exception;
use Throwable;

class ModelMemberContractNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('membership::base.exceptions.model_member_contract_not_found', [
            'model' => $model
        ]), $code, $previous);
    }
}
