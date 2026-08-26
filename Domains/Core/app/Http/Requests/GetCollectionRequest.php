<?php

namespace Domains\Core\Http\Requests;

use Kalimulhaq\Qubuilder\Http\Requests\GetCollectionRequest as QubuilderGetCollectionRequest;

/**
 * App-wide base for list/index endpoints. Every domain's own GetCollectionRequest
 * should extend this rather than the package class directly.
 *
 * Loosens the package's strict structural validation (ValidateFilter/ValidateSort/
 * ValidateInclude/ValidateStringArray) down to a generic "is this valid JSON" check —
 * qubuilder's own query-building code still enforces the real shape when it parses
 * each parameter, so this only changes what a caller sees as a 422 vs a runtime no-op.
 */
class GetCollectionRequest extends QubuilderGetCollectionRequest
{
    //
}
