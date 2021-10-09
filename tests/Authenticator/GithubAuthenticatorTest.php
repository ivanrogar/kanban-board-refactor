<?php

declare(strict_types=1);

namespace KanbanBoard\Tests\Authenticator;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use KanbanBoard\Authenticator\GithubAuthenticator;
use KanbanBoard\Exception\Authenticator\AuthenticationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @covers \KanbanBoard\Authenticator\GithubAuthenticator
 */
class GithubAuthenticatorTest extends TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var GithubAuthenticator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->session  = $this->createMock(SessionInterface::class);

        $this->mockHandler = new MockHandler();

        $this->client = new Client(['handler' => $this->mockHandler]);

        $this->subject = new GithubAuthenticator($this->session, $this->client);
    }

    public function testIsAuthenticated()
    {
        $this
            ->session
            ->expects($this->once())
            ->method('get')
            ->with(
                GithubAuthenticator::ACCESS_TOKEN_KEY
            )
            ->willReturn('some token');

        $this->assertTrue($this->subject->isAuthenticated());
    }

    public function testGetAccessToken()
    {
        $this
            ->session
            ->expects($this->once())
            ->method('get')
            ->with(
                GithubAuthenticator::ACCESS_TOKEN_KEY
            )
            ->willReturn('some token');

        $this->assertSame(
            'some token',
            $this->subject->getAccessToken()
        );
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertInstanceOf(RedirectResponse::class, $this->subject->getAuthorizationUrl());
    }

    /**
     * @covers \KanbanBoard\Authenticator\GithubAuthenticator::authenticate
     */
    public function testAuthenticateWillFail()
    {
        $request = new Request(
            ['error_description' => 'some description']
        );

        $this->expectException(AuthenticationFailedException::class);

        $this->subject->authenticate($request);
    }

    /**
     * @covers \KanbanBoard\Authenticator\GithubAuthenticator::authenticate
     * @throws AuthenticationFailedException
     */
    public function testAuthenticateWillSucceed()
    {
        $request = new Request();

        $this->mockHandler->append(
            new Response(
                200,
                [],
                \json_encode(
                    [
                        'access_token' => 'some token',
                    ]
                )
            )
        );

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with(
                GithubAuthenticator::ACCESS_TOKEN_KEY,
                'some token'
            );

        $this->subject->authenticate($request);
    }
}
