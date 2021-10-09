<?php

declare(strict_types=1);

namespace KanbanBoard\Factory;

use KanbanBoard\Contract\GitInterface;
use KanbanBoard\Contract\Factory\Git\GithubFactoryInterface;
use KanbanBoard\Exception\Git\UnsupportedTypeException;

class GitFactory
{
    public const TYPE_GITHUB = 'github';
    public const TYPE_BITBUCKET = 'bitbucket';
    public const TYPE_GITLAB = 'gitlab';

    private GithubFactoryInterface $githubFactory;

    public function __construct(
        GithubFactoryInterface $githubFactory
    ) {
        $this->githubFactory = $githubFactory;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function create(string $type): GitInterface
    {
        return match ($type) {
            self::TYPE_GITHUB => $this->githubFactory->create(),
            default => throw new UnsupportedTypeException(
                sprintf(
                    'Unsupported type: %s',
                    $type
                )
            ),
        };
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function createDefault(): GitInterface
    {
        return $this->create($_ENV['GIT_TYPE']);
    }
}
