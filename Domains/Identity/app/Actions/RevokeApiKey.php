<?php

namespace Domains\Identity\Actions;

use Domains\Identity\Models\ApiKey;

class RevokeApiKey
{
    public function handle(ApiKey $apiKey): bool
    {
        return (bool) $apiKey->delete();
    }
}
