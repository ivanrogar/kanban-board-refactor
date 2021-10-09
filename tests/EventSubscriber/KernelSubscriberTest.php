<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\EventSubscriber;

use KanbanBoard\Application;
use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\EventSubscriber\KernelRequestSubscriber;
use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \KanbanBoard\EventSubscriber\KernelRequestSubscriber
 */
class KernelSubscriberTest extends TestCase
{
    /**
     * @var GitFactory|MockObject
     */
    private $gitFactory;

    /**
     * @var AuthenticatedGitInterface|MockObject
     */
    private $git;

    /**
     * @var AuthenticatorInterface|MockObject
     */
    private $authenticator;

    /**
     * @var RouterInterface|MockObject
     */
    private $router;

    /**
     * @var RequestEvent|MockObject
     */
    private $requestEvent;

    /**
     * @var KernelRequestSubscriber
     */
    private $subject;

    protected function setUp(): void
    {
        $this->gitFactory = $this->createMock(GitFactory::class);

        $this->git = $this->createMock(AuthenticatedGitInterface::class);

        $this->authenticator = $this->createMock(AuthenticatorInterface::class);

        $this->git->method('getAuthenticator')->willReturn($this->authenticator);

        $this->gitFactory->method('createDefault')->willReturn($this->git);

        $this->router = $this->createMock(RouterInterface::class);

        $this->requestEvent = $this->createMock(RequestEvent::class);

        $this->subject = new KernelRequestSubscriber(
            $this->gitFactory,
            $this->router
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            $this->subject::getSubscribedEvents(),
            [
                KernelEvents::REQUEST => 'onKernelRequest',
            ]
        );
    }

    /**
     * @throws UnsupportedTypeException
     * @covers \KanbanBoard\EventSubscriber\KernelRequestSubscriber::onKernelRequest
     */
    public function testOnKernelRequestWillNotSetResponse()
    {
        $this->authenticator->method('isAuthenticated')->willReturn(true);

        $request = new Request();

        $this->requestEvent->method('getRequest')->willReturn($request);

        $this->requestEvent->expects($this->never())->method('setResponse');

        $this->subject->onKernelRequest($this->requestEvent);
    }

    /**
     * @throws UnsupportedTypeException
     * @covers \KanbanBoard\EventSubscriber\KernelRequestSubscriber::onKernelRequest
     */
    public function testOnKernelRequestWillSetResponse()
    {
        $this->authenticator->method('isAuthenticated')->willReturn(false);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/'
            ]
        );

        $this->requestEvent->method('getRequest')->willReturn($request);

        $this->requestEvent->expects($this->once())->method('setResponse');

        $this->router->method('match')->willReturn([
            '_route' => Application::ROUTE_BOARD,
        ]);

        $this->router->method('generate')->willReturn('/');

        $this->subject->onKernelRequest($this->requestEvent);
    }
}
