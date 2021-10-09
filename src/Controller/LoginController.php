<?php

declare(strict_types=1);

namespace KanbanBoard\Controller;

use KanbanBoard\Application;
use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Exception\Authenticator\AuthenticationFailedException;
use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractController
{
    private GitFactory $gitFactory;

    public function __construct(GitFactory $gitFactory)
    {
        $this->gitFactory = $gitFactory;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function loginAction(): RedirectResponse
    {
        $git = $this->gitFactory->createDefault();

        if ($git instanceof AuthenticatedGitInterface) {
            $authenticator = $git->getAuthenticator();

            if (!$authenticator->isAuthenticated()) {
                return $authenticator->getAuthorizationUrl();
            }
        }

        return new RedirectResponse(
            $this->generateUrl(Application::ROUTE_BOARD)
        );
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function redirectAction(Request $request): Response | RedirectResponse
    {
        $git = $this->gitFactory->createDefault();

        if ($git instanceof AuthenticatedGitInterface) {
            try {
                $git->getAuthenticator()->authenticate($request);
            } catch (AuthenticationFailedException $exception) {
                return new Response(
                    sprintf(
                        'Authentication failed: %s',
                        $exception->getMessage()
                    ),
                    401
                );
            }
        }

        return new RedirectResponse(
            $this->generateUrl(Application::ROUTE_BOARD)
        );
    }
}
