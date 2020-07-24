<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class PermissionRefresh extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $guards = [];

    protected $permissions = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('permission:refresh');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('refresh permission via routers');
    }

    public function handle()
    {
        $this->guards = config('auth.guards');

        $factory = $this->container->get(DispatcherFactory::class);
        $router  = $factory->getRouter('http');
        [$routers, $regRouters] = $router->getData();

        $serverName = 'http';

        foreach ($routers as $method => $items) {
            foreach ($items as $item) {
                $uri = $item->route;
                if (is_array($item->callback)) {
                    $action = $item->callback[0] . '::' . $item->callback[1];
                } else {
                    $action = $item->callback;
                }
                $middlewares = MiddlewareManager::get($serverName, $uri, $method);
                $this->matchRoute($action, $middlewares);
            }
        }

        foreach ($regRouters as $method => $items) {
            foreach ($items as $item) {
                foreach ($item['routeMap'] as $route_item) {
                    $handle = array_shift($route_item);
                    $uri    = $handle->route;

                    if (is_array($handle->callback)) {
                        $action = $handle->callback[0] . '::' . $handle->callback[1];
                    } else {
                        $action = $handle->callback;
                    }
                    $middlewares = MiddlewareManager::get($serverName, $uri, $method);
                    $this->matchRoute($action, $middlewares);
                }
            }
        }

        $data            = [];
        $permissionClass = make(config('permission.models.permission'));

        foreach ($this->permissions as $guard => $permissions) {
            $data[] = [$guard, count($permissions)];
            foreach ($permissions as $permission) {
                $permissionClass->findOrCreate($permission, $guard);
            }
        }
        $this->table(['Guard', 'Total Permission'], $data);
        $this->info("success.");
    }

    public function matchRoute(string $action, array $middlewares)
    {
        foreach ($this->guards as $guard_name => $guard) {
            $middleware = $guard['middleware'];
            if (in_array($middleware, $middlewares)) {
                $nameSpace = ucfirst(Str::camel($guard_name));
                preg_match("/App\\\Controller\\\\" . $nameSpace . "\\\\([a-zA-Z]+)Controller@([a-zA-Z]+)/", $action, $matchs);
                if (count($matchs) !== 3) {
                    $this->error("the acttion {$action} not match");
                    continue;
                }
                $simply_action                    = $matchs[1] . "@" . $matchs[2];
                $this->permissions[$guard_name][] = $simply_action;
            }
        }
    }
}
