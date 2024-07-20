<?php

namespace JobMetric\Membership;

use JobMetric\Membership\Models\Member as MemberModel;

/**
 * Trait CanMember
 *
 * @package JobMetric\Membership
 *
 * @property MemberModel member
 * @property MemberModel[] members
 *
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait CanMember
{
}
