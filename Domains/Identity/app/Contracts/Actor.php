<?php

namespace Domains\Identity\Contracts;

interface Actor
{
    public function getCompanyId(): ?int;

    public function isRoot(): bool;

    public function isOwner(): bool;

    public function actorLabel(): string;   // audit trail

    public function can(...$permissions): bool;
}
