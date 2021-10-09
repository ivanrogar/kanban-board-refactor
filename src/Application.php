<?php

declare(strict_types=1);

namespace KanbanBoard;

use KanbanBoard\Controller\BoardController;
use KanbanBoard\Controller\LoginController;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container
            ->services()
            ->set('event_dispatcher', EventDispatcher::class);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes
            ->add(
                self::ROUTE_OAUTH_INDEX,
                '/login/oauth'
            )
            ->controller(
                [
                    LoginController::class,
                    'loginAction'
                ]
            )
            ->add(
                self::ROUTE_OAUTH_REDIRECT_INDEX,
                '/login/oauth/redirect'
            )
            ->controller(
                [
                    LoginController::class,
                    'redirectAction'
                ]
            )
            ->add(
                self::ROUTE_BOARD,
                '/'
            )
            ->controller(
                [
                    BoardController::class,
                    'boardAction'
                ]
            );
    }
}
