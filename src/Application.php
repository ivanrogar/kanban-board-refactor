<?php

declare(strict_types=1);

namespace KanbanBoard;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Application extends BaseKernel
{
    use MicroKernelTrait;

    public const SESSION_ACCESS_TOKEN = '__access_token';

    public const ROUTE_OAUTH_INDEX = 'oauth_index';
    public const ROUTE_OAUTH_REDIRECT_INDEX = 'oauth_redirect_index';
    public const ROUTE_BOARD = 'board_index';

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $services = $container->services();

        $services
            ->set('event_dispatcher', EventDispatcher::class)
            ->set('session', Session::class)
            ->set(SessionInterface::class, Session::class)
            ->set(Client::class, Client::class);

        $defaults = $services->defaults();

        $defaults
            ->autowire(true)
            ->autoconfigure(true)
            ->load('KanbanBoard\\', '.');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes
            ->add(
                self::ROUTE_OAUTH_INDEX,
                '/login/oauth'
            )
            ->controller(
                'KanbanBoard\Controller\LoginController::loginAction'
            )
            ->add(
                self::ROUTE_OAUTH_REDIRECT_INDEX,
                '/login/oauth/redirect'
            )
            ->controller(
                'KanbanBoard\Controller\LoginController::redirectAction'
            )
            ->add(
                self::ROUTE_BOARD,
                '/'
            )
            ->controller(
                'KanbanBoard\Controller\BoardController::boardAction',
            );
    }
}
