<?php

declare(strict_types=1);

namespace KanbanBoard\Model\Git;

use Github\Client;
use Github\Api\Issue;
use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Factory\Client\Github\ClientFactory;

class Github implements AuthenticatedGitInterface
{
    private ClientFactory $clientFactory;
    private AuthenticatorInterface $authenticator;

    private ?Client $client = null;
    private ?Issue $issueClient = null;
    private ?Issue\Milestones $milestonesClient = null;

    public function __construct(
        ClientFactory $clientFactory,
        AuthenticatorInterface $authenticator
    ) {
        $this->clientFactory = $clientFactory;
        $this->authenticator = $authenticator;
    }

    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    public function getMilestones(string $repository): array
    {
        return $this
            ->getMilestonesClient()
            ->all(
                getenv('GH_ACCOUNT'),
                $repository
            );
    }

    public function getIssues(string $repository, mixed $mileStoneId): array
    {
        $params = [
            'milestone' => $mileStoneId,
            'state' => 'all'
        ];

        return $this
            ->getIssueClient()
            ->all(
                getenv('GH_ACCOUNT'),
                $repository,
                $params
            );
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = $this->clientFactory->create();
        }

        return $this->client;
    }

    private function getIssueClient(): Issue
    {
        if ($this->issueClient === null) {
            /**
             * @var Issue $client
             */
            $client = $this->getClient()->api('issues');

            $this->issueClient = $client;
        }

        return $this->issueClient;
    }

    private function getMilestonesClient(): Issue\Milestones
    {
        if ($this->milestonesClient === null) {
            $this->milestonesClient = $this->getIssueClient()->milestones();
        }

        return $this->milestonesClient;
    }
}
