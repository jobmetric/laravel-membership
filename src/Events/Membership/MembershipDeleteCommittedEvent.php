<?php

namespace JobMetric\Membership\Events\Membership;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Membership deleted outcome using allowlisted identifiers.
 */
readonly class MembershipDeleteCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param string $personType
     * @param int $personId
     * @param string $ownerType
     * @param int $ownerId
     * @param ?string $collection
     *
     * @return void
     */
    public function __construct(
        public string $personType,
        public int $personId,
        public string $ownerType,
        public int $ownerId,
        public ?string $collection
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'membership.membership.deleted.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'membership::automation.membership_deleted.group', 'membership::automation.membership_deleted.title', 'membership::automation.membership_deleted.description', 'fas fa-bolt', ['automation', 'committed', 'membership']);
    }
}
