<?php

declare(strict_types=1);

namespace KanbanBoard\Factory\Git;

use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Contract\Factory\Git\GithubFactoryInterface;
use KanbanBoard\Factory\Client\Github\ClientFactory;
use KanbanBoard\Model\Git\Github;

class GithubFactory implements GithubFactoryInterface
{
    private ClientFactory $clientFactory;
    private AuthenticatorInterface $authenticator;

    public function __construct(
        ClientFactory $clientFactory,
        AuthenticatorInterface $authenticator
    ) {
        $this->clientFactory = $clientFactory;
        $this->authenticator = $authenticator;
    }

    public function create(): Github
    {
        return new Github($this->clientFactory, $this->authenticator);
    }
}
