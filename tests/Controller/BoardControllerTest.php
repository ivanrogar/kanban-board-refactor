<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Controller;

use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Controller\BoardController;
use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use KanbanBoard\Model\Git\Github;
use KanbanBoard\Tests\Model\Git\GithubBase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \KanbanBoard\Controller\BoardController
 */
class BoardControllerTest extends GithubBase
{
    /**
     * @var GitFactory|MockObject
     */
    protected $gitFactory;

    /**
     * @var AuthenticatedGitInterface|MockObject
     */
    protected $git;

    /**
     * @var BoardControllerTest
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gitFactory = $this->createMock(GitFactory::class);

        $this->git = new Github(
            $this->clientFactory,
            $this->authenticator
        );

        $this->gitFactory->method('createDefault')->willReturn($this->git);

        $this->subject = new BoardController(
            $this->gitFactory
        );
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testBoardAction()
    {
        $response = $this->subject->boardAction();

        $this->assertSame(200, $response->getStatusCode());
    }
}
