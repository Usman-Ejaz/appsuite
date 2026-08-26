<?php

namespace App\Enums;

/**
 * Stable, machine-readable error codes returned on the response envelope (`code`)
 * for auth/authorization failures, so clients can branch on a fixed contract
 * instead of matching the free-text `message`.
 */
enum ApiErrorCode: string
{
    case UNAUTHENTICATED = 'UNAUTHENTICATED';
    case FORBIDDEN = 'FORBIDDEN';

    /**
     * Transport header used by abort() call sites to pass a code through to
     * ForceJsonResponse, which folds it into the body and strips the header.
     */
    public const HEADER = 'X-Auth-Reason';

    /**
     * Generic code for an HTTP status when a call site didn't set a specific one,
     * so every 401/403 still carries a code. Null for statuses we don't tag.
     */
    public static function defaultFor(int $status): ?string
    {
        return match ($status) {
            401 => self::UNAUTHENTICATED->value,
            403 => self::FORBIDDEN->value,
            default => null,
        };
    }
}
