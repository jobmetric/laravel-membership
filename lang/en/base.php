<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Membership Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Membership for
    | various messages that we need to display to the user.
    |
    */

    'validation' => [
        'errors' => 'Validation errors occurred.',
        'member_collection_exists' => 'The :collection collection already exists.',
        'member_collection_not_found' => 'The :collection collection not found.',
    ],

    "exceptions" => [
        'model_member_contract_not_found' => 'Model ":model" not implements "JobMetric\Membership\Contracts\MemberContract" interface!',
        'trait_can_member_not_found_in_model' => 'Trait "JobMetric\Membership\CanMember" not found in model ":model"!',
        'trait_has_member_not_found_in_model' => 'Trait "JobMetric\Membership\HasMember" not found in model ":model"!',
        'member_collection_not_allowed' => 'Model ":model" not allowed to have ":collection" collection!',
        'member_collection_type_not_match' => 'The ":collection" type of the ":model" model must be "single" or "multiple"!',
        'member_expired_at_is_past' => 'The given date is ":expired_at", you must enter a time in the future!',
    ],

    'messages' => [
        'created' => 'The member was created successfully.',
        'deleted' => 'The member was deleted successfully.',
    ]

];
