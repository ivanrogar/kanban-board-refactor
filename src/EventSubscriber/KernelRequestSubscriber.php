<?php

/**
 * Since we're not using a full-fledged firewall-based security at this point,
 * this event subscriber just makes sure that unauthenticated users are being
 * redirected to the login route
 */

declare(strict_types=1);

namespace KanbanBoard\EventSubscriber;

use KanbanBoard\Application;
use KanbanBoard\Contract\AuthenticatedGitInterface;
use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    private GitFactory $gitFactory;
    private RouterInterface $router;

    public function __construct(
        GitFactory $gitFactory,
        RouterInterface $router
    ) {
        $this->gitFactory = $gitFactory;
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $git = $this->gitFactory->createDefault();

        if ($git instanceof AuthenticatedGitInterface) {
            $authenticator = $git->getAuthenticator();

            if (!$authenticator->isAuthenticated()) {
                $request = $event->getRequest();

                try {
                    $match = $this->router->match($request->getPathInfo());

                    $route = $match['_route'];

                    if (!in_array($route, [Application::ROUTE_OAUTH_INDEX, Application::ROUTE_OAUTH_REDIRECT_INDEX])) {
                        $event->setResponse(
                            new RedirectResponse(
                                $this->router->generate(Application::ROUTE_OAUTH_INDEX)
                            )
                        );
                    }
                } catch (NoConfigurationException | ResourceNotFoundException | MethodNotAllowedException) {
                }
            }
        }
    }
}
