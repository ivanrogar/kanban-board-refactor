<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Model\Git;

use Github\Api\Issue;
use Github\Client;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Factory\Client\Github\ClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class GithubBase extends TestCase
{
    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @var ClientFactory|MockObject
     */
    protected $clientFactory;

    /**
     * @var Issue|MockObject
     */
    protected $issueClient;

    /**
     * @var Issue\Milestones|MockObject
     */
    protected $milestonesClient;

    /**
     * @var AuthenticatorInterface|MockObject
     */
    protected $authenticator;

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
    }
}
