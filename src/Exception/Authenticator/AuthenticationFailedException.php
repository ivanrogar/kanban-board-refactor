<?php

declare(strict_types=1);

namespace KanbanBoard\Exception\Authenticator;

use KanbanBoard\Exception\BaseException;
use Throwable;

class AuthenticationFailedException extends BaseException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
