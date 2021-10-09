<?php

declare(strict_types=1);

namespace KanbanBoard\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Issue extends DataTransferObject
{
    public const STATE_QUEUED = 'queued';
    public const STATE_ACTIVE = 'active';
    public const STATE_COMPLETED = 'completed';

    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string|int
     */
    public $number;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $url;

    public ?string $assignee = null;

    public bool $paused = false;
    public bool $closed = false;

    /**
     * @var Progress
     */
    public $progress;
}
