<?php

declare(strict_types=1);

namespace KanbanBoard\Exception\Git;

use KanbanBoard\Exception\BaseException;
use Throwable;

class UnsupportedTypeException extends BaseException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
