<?php

declare(strict_types=1);

namespace KanbanBoard\Model\Git;

use Github\Client;
use Github\Api\Issue;
use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Data\Milestone;
use KanbanBoard\Data\Progress;
use KanbanBoard\Factory\Client\Github\ClientFactory;
use Michelf\Markdown;

class Github implements AuthenticatedGitInterface
{
    private ClientFactory $clientFactory;
    private AuthenticatorInterface $authenticator;

    private ?Client $client = null;
    private ?Issue $issueClient = null;
    private ?Issue\Milestones $milestonesClient = null;

    private array $pausedLabels = [];

    public function __construct(
        ClientFactory $clientFactory,
        AuthenticatorInterface $authenticator,
    ) {
        $this->clientFactory = $clientFactory;
        $this->authenticator = $authenticator;
        $this->pausedLabels = explode(',', $_ENV['GH_PAUSED_LABELS']);
    }

    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    public function getMilestones(string $repository): array
    {
        $response = $this
            ->getMilestonesClient()
            ->all(
                $_ENV['GH_ACCOUNT'],
                $repository
            );

        $milestoneData = [];

        foreach ($response as $milestoneItem) {
            $issues = $this->getIssues($repository, $milestoneItem['number']);

            if (empty($issues)) {
                continue;
            }

            $progress = $this->getProgress($milestoneItem['closed_issues'], $milestoneItem['open_issues']);

            if ($progress->total === 0) {
                continue;
            }

            $milestone = new Milestone();

            $milestone->id = $milestoneItem['id'];
            $milestone->title = $milestoneItem['title'];
            $milestone->url = $milestoneItem['url'];
            $milestone->repository = $repository;
            $milestone->progress = $progress;

            $milestone->queued = array_filter($issues, function (\KanbanBoard\Data\Issue $issue) {
                return $issue->state === \KanbanBoard\Data\Issue::STATE_QUEUED;
            });

            $active = array_filter($issues, function (\KanbanBoard\Data\Issue $issue) {
                return $issue->state === \KanbanBoard\Data\Issue::STATE_ACTIVE;
            });

            usort($active, function (\KanbanBoard\Data\Issue $first, \KanbanBoard\Data\Issue $second) {
                return (!$first->paused && !$second->paused)
                    ? strcmp($first->title, $second->title)
                    : (int)$first->paused <=> (int)$second->paused;
            });

            $milestone->active = $active;

            $milestone->completed = array_filter($issues, function (\KanbanBoard\Data\Issue $issue) {
                return $issue->state === \KanbanBoard\Data\Issue::STATE_COMPLETED;
            });

            $milestoneData[] = $milestone;
        }

        usort($milestoneData, function (Milestone $first, Milestone $second) {
            return strcmp($first->title, $second->title);
        });

        return $milestoneData;
    }

    public function getIssues(string $repository, mixed $mileStoneId): array
    {
        $params = [
            'milestone' => $mileStoneId,
            'state' => 'all'
        ];

        $response = $this
            ->getIssueClient()
            ->all(
                $_ENV['GH_ACCOUNT'],
                $repository,
                $params
            );

        $issues = [];

        foreach ($response as $issueItem) {
            if (array_key_exists('pull_request', $issueItem)) {
                continue;
            }

            $issue = new \KanbanBoard\Data\Issue();

            $issue->id = $issueItem['id'];
            $issue->number = $issueItem['number'];
            $issue->title = str_replace('/', ' ', $issueItem['title']);
            $issue->body = Markdown::defaultTransform($issueItem['body']);
            $issue->url = $issueItem['html_url'];

            $assignee = $issueItem['assignee'] ?? [];

            $issue->assignee = (array_key_exists('avatar_url', $assignee))
                ? $assignee['avatar_url'] . '?s=16'
                : null;

            $state = \KanbanBoard\Data\Issue::STATE_QUEUED;

            if ($issueItem['state'] === 'closed') {
                $state = \KanbanBoard\Data\Issue::STATE_COMPLETED;
            } elseif (isset($issueItem['assignee'])) {
                $state = \KanbanBoard\Data\Issue::STATE_ACTIVE;
            }

            $issue->paused = $this->hasLabels($issueItem, $this->pausedLabels);

            $issue->state = $state;

            $complete = substr_count(strtolower($issueItem['body']), '[x]');
            $remaining = substr_count(strtolower($issueItem['body']), '[ ]');

            $issue->progress = $this->getProgress($complete, $remaining);

            $issue->closed = array_key_exists('closed_at', $issueItem);

            $issues[] = $issue;
        }

        return $issues;
    }

    private function hasLabels(array $issueItem, array $labels): bool
    {
        $issueLabels = $issueItem['labels'] ?? [];

        foreach ($issueLabels as $issueLabel) {
            if (in_array($issueLabel['name'], $labels)) {
                return true;
            }
        }

        return false;
    }

    private function getProgress(int $complete, int $remaining): Progress
    {
        $progress = new Progress();

        $total = $complete + $remaining;

        if ($total > 0) {
            $percent = ($complete || $remaining) ? round($complete / $total * 100) : 0;

            $progress->total = $total;
            $progress->complete = $complete;
            $progress->remaining = $remaining;
            $progress->percent = $percent;
        }

        return $progress;
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = $this->clientFactory->create();

            $this
                ->client
                ->authenticate(
                    $this->authenticator->getAccessToken(),
                    null,
                    Client::AUTH_ACCESS_TOKEN
                );
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
