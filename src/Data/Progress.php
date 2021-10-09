<?php

declare(strict_types=1);

namespace KanbanBoard\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Progress extends DataTransferObject
{
    public int | float $total = 0;
    public int | float $complete = 0;
    public int | float $remaining = 0;
    public int | float $percent = 0;
}
