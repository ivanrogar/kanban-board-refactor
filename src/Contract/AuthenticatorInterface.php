<?php

declare(strict_types=1);

namespace KanbanBoard\Contract;

use KanbanBoard\Exception\Authenticator\AuthenticationFailedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{
    public function isAuthenticated(): bool;

    public function getAccessToken(): ?string;

    public function getAuthorizationUrl(): RedirectResponse;

    /**
     * @throws AuthenticationFailedException
     */
    public function authenticate(Request $request): void;
}
