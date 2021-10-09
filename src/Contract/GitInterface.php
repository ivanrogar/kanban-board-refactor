<?php

declare(strict_types=1);

namespace KanbanBoard\Contract;

interface GitInterface
{
    public function getMilestones(string $repository): array;

    public function getIssues(string $repository, mixed $mileStoneId): array;
}
