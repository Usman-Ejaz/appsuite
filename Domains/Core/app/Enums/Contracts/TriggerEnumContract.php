<?php

namespace Domains\Core\Enums\Contracts;

interface TriggerEnumContract
{
    public function app_code(): ?string;

    public function label(): string;

    public function description(): string;
}
