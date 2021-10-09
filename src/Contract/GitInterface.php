<?php

declare(strict_types=1);

namespace KanbanBoard\Contract;

use KanbanBoard\Data\Issue;
use KanbanBoard\Data\Milestone;

interface GitInterface
{
    /**
     * @param string $repository
     * @return Milestone[]
     */
    public function getMilestones(string $repository): array;

    /**
     * @param string $repository
     * @param mixed $mileStoneId
     * @return Issue[]
     */
    public function getIssues(string $repository, mixed $mileStoneId): array;
}
