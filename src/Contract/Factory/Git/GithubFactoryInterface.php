<?php

declare(strict_types=1);

namespace KanbanBoard\Contract\Factory\Git;

use KanbanBoard\Model\Git\Github;

interface GithubFactoryInterface
{
    public function create(): Github;
}
