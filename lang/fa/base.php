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

    "validation" => [
        "member_collection_exists" => "مجموعه :collection از قبل وجود دارد.",
        "member_collection_not_found" => "مجموعه :collection یافت نشد.",
    ],

    "exceptions" => [
        "model_member_contract_not_found" => "مدل ':model' اینترفیس 'JobMetric\Membership\Contracts\MemberContract' را پیاده‌سازی نکرده است!",
        "trait_can_member_not_found_in_model" => "Trait 'JobMetric\Membership\CanMember' در مدل ':model' یافت نشد!",
        "trait_has_member_not_found_in_model" => "Trait 'JobMetric\Membership\HasMember' در مدل ':model' یافت نشد!",
        "member_collection_not_allowed" => "مدل ':model' مجاز به داشتن مجموعه ':collection' نیست!",
        "member_collection_type_not_match" => "نوع ':collection' در مدل ':model' باید 'single' یا 'multiple' باشد!",
        "member_expired_at_is_past" => "تاریخ وارد شده ':expired_at' است، شما باید زمانی در آینده وارد کنید!",
    ],

    "messages" => [
        "created" => "عضو با موفقیت ایجاد شد.",
        "deleted" => "عضو با موفقیت حذف شد.",
    ],

];
