<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Controller;

use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Controller\LoginController;
use KanbanBoard\Exception\Authenticator\AuthenticationFailedException;
use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \KanbanBoard\Controller\LoginController
 */
class LoginControllerTest extends TestCase
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
     * @var LoginController
     */
    private $subject;

    protected function setUp(): void
    {
        $this->gitFactory = $this->createMock(GitFactory::class);

        $this->git = $this->createMock(AuthenticatedGitInterface::class);

        $this->authenticator = $this->createMock(AuthenticatorInterface::class);

        $this->git->method('getAuthenticator')->willReturn($this->authenticator);

        $this->gitFactory->method('createDefault')->willReturn($this->git);

        $this->subject = $this
            ->getMockBuilder(LoginController::class)
            ->onlyMethods(
                [
                    'generateUrl'
                ]
            )
            ->setConstructorArgs(
                [
                    $this->gitFactory
                ]
            )
            ->getMock();
    }

    /**
     * @covers \KanbanBoard\Controller\LoginController::loginAction
     * @throws UnsupportedTypeException
     */
    public function testLoginActionWillRedirectToAuthUrl()
    {
        $this->authenticator->method('isAuthenticated')->willReturn(false);

        $this
            ->authenticator
            ->expects($this->once())
            ->method('getAuthorizationUrl');

        $this->assertInstanceOf(RedirectResponse::class, $this->subject->loginAction());
    }

    /**
     * @covers \KanbanBoard\Controller\LoginController::loginAction
     * @throws UnsupportedTypeException
     */
    public function testLoginActionWillRedirectToBoard()
    {
        $this->authenticator->method('isAuthenticated')->willReturn(true);

        $this
            ->authenticator
            ->expects($this->never())
            ->method('getAuthorizationUrl');

        $this->subject->method('generateUrl')->willReturn('/login/oauth');

        $response = $this->subject->loginAction();

        $this->assertTrue($response->isRedirect('/login/oauth'));
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testRedirectActionFailure()
    {
        $request = new Request();

        $this->subject->method('generateUrl')->willReturn('/');

        $this
            ->authenticator
            ->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new AuthenticationFailedException());

        $response = $this->subject->redirectAction($request);

        $this->assertTrue(!$response->isRedirection());

        $this->assertSame(401, $response->getStatusCode());
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testRedirectActionSuccess()
    {
        $request = new Request();

        $this->subject->method('generateUrl')->willReturn('/');

        $this->authenticator->expects($this->once())->method('authenticate');

        $response = $this->subject->redirectAction($request);

        $this->assertTrue($response->isRedirect('/'));
    }
}
