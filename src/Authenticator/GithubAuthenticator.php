<?php

declare(strict_types=1);

namespace KanbanBoard\Authenticator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use KanbanBoard\Contract\AuthenticatorInterface;
use KanbanBoard\Exception\Authenticator\AuthenticationFailedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GithubAuthenticator implements AuthenticatorInterface
{
    public const ACCESS_TOKEN_KEY = '__gh_access_token';

    public const BASE_URL = 'https://github.com/';
    public const AUTHORIZE_URL = self::BASE_URL . 'login/oauth/authorize';
    public const ACCESS_TOKEN_URL = self::BASE_URL . 'login/oauth/access_token';

    private SessionInterface $session;
    private Client $client;

    public function __construct(
        SessionInterface $session,
        Client $client,
    ) {
        $this->session = $session;
        $this->client = $client;
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
            'client_id' => $_ENV['GH_CLIENT_ID'],
            'scope' => 'repo',
            'state' => $_ENV['GH_STATE']
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
        if ($request->query->has('error_description')) {
            throw new AuthenticationFailedException($request->query->get('error_description'));
        }

        $data = [
            'code' => $request->get('code'),
            'state' => $request->get('state'),
            'client_id' => $_ENV['GH_CLIENT_ID'],
            'client_secret' => $_ENV['GH_CLIENT_SECRET'],
        ];

        $url = self::ACCESS_TOKEN_URL . '?' . http_build_query($data);

        try {
            $response = $this
                ->client
                ->request(
                    'POST',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json'
                        ]
                    ]
                );

            $data = \json_decode($response->getBody()->getContents(), true);

            $token = $data['access_token'];

            $this->session->set(self::ACCESS_TOKEN_KEY, $token);
        } catch (GuzzleException $exception) {
            throw new AuthenticationFailedException($exception->getMessage(), 0, $exception);
        }
    }
}
