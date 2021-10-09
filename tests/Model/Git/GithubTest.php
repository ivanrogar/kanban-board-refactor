<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Model\Git;

use Github\Api\Issue;
use Github\Client;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Factory\Client\Github\ClientFactory;
use KanbanBoard\Model\Git\Github;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \KanbanBoard\Model\Git\Github
 */
class GithubTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var ClientFactory|MockObject
     */
    private $clientFactory;

    /**
     * @var Issue|MockObject
     */
    private $issueClient;

    /**
     * @var Issue\Milestones|MockObject
     */
    private $milestonesClient;

    /**
     * @var AuthenticatorInterface|MockObject
     */
    private $authenticator;

    /**
     * @var Github
     */
    private $subject;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->clientFactory = $this->createMock(ClientFactory::class);
        $this->clientFactory->method('create')->willReturn($this->client);

        $this->issueClient = $this->createMock(Issue::class);
        $this->milestonesClient = $this->createMock(Issue\Milestones::class);

        $this
            ->client
            ->method('api')
            ->with(
                'issues'
            )
            ->willReturn($this->issueClient);

        $this->issueClient->method('milestones')->willReturn($this->milestonesClient);

        $this
            ->milestonesClient
            ->method('all')
            ->willReturnCallback(
                function () {
                    return \json_decode(
                        \file_get_contents(
                            dirname(__FILE__) . '/../../files/milestones.response.json'
                        ),
                        true
                    );
                }
            );

        $this
            ->issueClient
            ->method('all')
            ->willReturnCallback(
                function () {
                    return \json_decode(
                        \file_get_contents(
                            dirname(__FILE__) . '/../../files/issues.response.json'
                        ),
                        true
                    );
                }
            );

        $this->authenticator = $this->createMock(AuthenticatorInterface::class);

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
