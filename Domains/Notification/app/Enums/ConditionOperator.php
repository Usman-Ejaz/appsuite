<?php

namespace Domains\Notification\Enums;

/**
 * A comparison a trigger action condition rule makes against a token's value.
 */
enum ConditionOperator: string
{
    /**
     * Equal to the value.
     */
    case EQ = 'Eq';

    /**
     * Not equal to the value.
     */
    case NEQ = 'Neq';

    /**
     * Greater than the value. Numbers and money only.
     */
    case GT = 'Gt';

    /**
     * Greater than or equal to the value. Numbers and money only.
     */
    case GTE = 'Gte';

    /**
     * Less than the value. Numbers and money only.
     */
    case LT = 'Lt';

    /**
     * Less than or equal to the value. Numbers and money only.
     */
    case LTE = 'Lte';

    /**
     * One of a list of values.
     */
    case IN = 'In';

    /**
     * None of a list of values.
     */
    case NOT_IN = 'NotIn';

    /**
     * Contains the value, as text or as a list item.
     */
    case CONTAINS = 'Contains';

    /**
     * Starts with the value.
     */
    case STARTS_WITH = 'StartsWith';

    /**
     * Has no value.
     */
    case IS_EMPTY = 'IsEmpty';

    /**
     * Has a value.
     */
    case IS_NOT_EMPTY = 'IsNotEmpty';

    /**
     * Changed to the value in this event.
     */
    case CHANGED_TO = 'ChangedTo';

    /**
     * Changed away from the value in this event.
     */
    case CHANGED_FROM = 'ChangedFrom';

    /**
     * Whether the operator only makes sense for numeric tokens.
     */
    public function isNumeric(): bool
    {
        return in_array($this, [self::GT, self::GTE, self::LT, self::LTE], true);
    }

    /**
     * Whether the operator compares against a value at all.
     */
    public function needsValue(): bool
    {
        return ! in_array($this, [self::IS_EMPTY, self::IS_NOT_EMPTY], true);
    }
}
