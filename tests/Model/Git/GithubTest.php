<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Model\Git;

use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Model\Git\Github;

/**
 * @covers \KanbanBoard\Model\Git\Github
 */
class GithubTest extends GithubBase
{
    /**
     * @var Github
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Github(
            $this->clientFactory,
            $this->authenticator
        );
    }

    public function testGetAuthenticator()
    {
        $this->assertInstanceOf(AuthenticatorInterface::class, $this->subject->getAuthenticator());
    }

    public function testGetMilestones()
    {
        $this->client->expects($this->once())->method('authenticate');

        $milestones = $this->subject->getMilestones('repo');

        $this->assertCount(7, $milestones);
    }

    public function testGetIssues()
    {
        $this->client->expects($this->once())->method('authenticate');

        $issues = $this->subject->getIssues('repo', 1);

        $this->assertCount(2, $issues);
    }
}
