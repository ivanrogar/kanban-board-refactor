<?php

declare(strict_types=1);

namespace KanbanBoard\Contract;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

interface AuthenticatorInterface
{
    public function isAuthenticated(): bool;

    public function getAccessToken(): ?string;

    public function getAuthorizationUrl(): RedirectResponse;

    /**
     * @throws UnauthorizedHttpException
     */
    public function authenticate(Request $request): void;
}
