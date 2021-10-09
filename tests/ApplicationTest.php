<?php

declare(strict_types=1);

namespace KanbanBoard\Tests;

use KanbanBoard\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class ApplicationTest extends TestCase
{
    public function testWillBoot()
    {
        $appEnv = $_ENV['APP_ENVIRONMENT'];

        $this->assertInstanceOf(KernelInterface::class, new Application($appEnv, $appEnv !== 'prod'));
    }
}
