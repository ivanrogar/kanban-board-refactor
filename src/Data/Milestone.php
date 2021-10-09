<?php

declare(strict_types=1);

namespace KanbanBoard\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Milestone extends DataTransferObject
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $repository;

    /**
     * @var Issue[]
     */
    public array $queued = [];

    /**
     * @var Issue[]
     */
    public array $active = [];

    /**
     * @var Issue[]
     */
    public array $completed = [];

    /**
     * @var Progress
     */
    public $progress;
}
