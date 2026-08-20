<?php

namespace Domains\Identity\Actions;

use Domains\Identity\Models\ApiKey;
use Domains\Identity\Models\Company;

class IssueApiKey
{
    /**
     * @param  array{name: string, abilities?: array|null, expires_at?: string|null}  $data
     * @return array{apiKey: ApiKey, plainSecret: string}
     */
    public function handle(Company $company, array $data): array
    {
        $credentials = ApiKey::generateCredentials();

        $apiKey = ApiKey::make([
            'company_id' => $company->id,
            'name' => $data['name'],
            'abilities' => $data['abilities'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        $apiKey->forceFill([
            'api_key' => $credentials['key'],
            'api_secret' => hash('sha256', $credentials['secret']),
        ])->save();

        return ['apiKey' => $apiKey, 'plainSecret' => $credentials['secret']];
    }
}
