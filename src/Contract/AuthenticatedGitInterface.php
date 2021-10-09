<?php

declare(strict_types=1);

namespace KanbanBoard\Contract;

interface AuthenticatedGitInterface extends GitInterface
{
    public function getAuthenticator(): AuthenticatorInterface;
}
