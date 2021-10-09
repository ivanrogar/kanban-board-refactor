<?php

declare(strict_types=1);

namespace KanbanBoard\Authenticator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use KanbanBoard\Application;
use KanbanBoard\Contract\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\RouterInterface;

class GithubAuthenticator implements AuthenticatorInterface
{
    public const ACCESS_TOKEN_KEY = '__gh_access_token';

    public const BASE_URL = 'https://github.com/';
    public const AUTHORIZE_URL = self::BASE_URL . 'login/oauth/authorize';
    public const ACCESS_TOKEN_URL = self::BASE_URL . 'login/oauth/access_token';

    private SessionInterface $session;
    private Client $client;
    private RouterInterface $router;

    public function __construct(
        SessionInterface $session,
        Client $client,
        RouterInterface $router
    ) {
        $this->session = $session;
        $this->client = $client;
        $this->router = $router;
    }

    public function isAuthenticated(): bool
    {
        return $this->getAccessToken() !== null;
    }

    public function getAccessToken(): ?string
    {
        return $this->session->get(self::ACCESS_TOKEN_KEY);
    }

    public function getAuthorizationUrl(): RedirectResponse
    {
        $url = self::AUTHORIZE_URL;

        $query = [
            'redirect_uri' => $this->router->generate(Application::ROUTE_OAUTH_REDIRECT_INDEX),
            'client_id' => getenv('GH_CLIENT_ID'),
            'scope' => 'repo',
            'state' => getenv('GH_STATE')
        ];

        return new RedirectResponse(
            $url . '?' . http_build_query($query)
        );
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): void
    {
        $data = [
            'code' => $request->get('code'),
            'state' => $request->get('state'),
            'client_id' => getenv('GH_CLIENT_ID'),
            'client_secret' => getenv('GH_CLIENT_SECRET'),
        ];

        $url = self::ACCESS_TOKEN_URL . '?' . http_build_query($data);

        try {
            $response = $this
                ->client
                ->request(
                    'POST',
                    $url
                );

            $data = \json_decode($response->getBody()->getContents(), true);

            $token = $data['access_token'];

            $this->session->set(self::ACCESS_TOKEN_KEY, $token);
        } catch (GuzzleException $exception) {
            throw new UnauthorizedHttpException('', $exception->getMessage(), $exception);
        }
    }
}
