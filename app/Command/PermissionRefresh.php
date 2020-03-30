<?php

declare(strict_types=1);

namespace App\Command;

use App\Middleware\AdminPermissionMiddleware;
use App\Model\Permission;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('permission:refresh');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('refresh permission from routers');
    }

    public function handle()
    {
        $factory = $this->container->get(DispatcherFactory::class);
        $router  = $factory->getRouter('http');
        [$routers, $regRouters] = $router->getData();

        $data = [];

        foreach ($routers as $method => $items) {
            foreach ($items as $item) {
                $uri        = $item->route;
                $serverName = 'http';
                if (is_array($item->callback)) {
                    $action = $item->callback[0] . '::' . $item->callback[1];
                } else {
                    $action = $item->callback;
                }
                $middlewares = MiddlewareManager::get('http', $uri, $method);
                if (in_array(AdminPermissionMiddleware::class, $middlewares)) {
                    preg_match("/App\\\Controller\\\Admin\\\([a-zA-Z]+)Controller@([a-zA-Z]+)/", $action, $matchs);
                    if (count($matchs) !== 3) {
                        $this->error("the acttion {$action} not match");
                        continue;
                    }
                    $simply_action = $matchs[1] . "@" . $matchs[2];
                    Permission::firstOrCreate([
                        'action' => $simply_action,
                    ], ['name' => $simply_action]);
                    $data[$method . '.' . $uri] = [
                        'server'     => $serverName,
                        'method'     => $method,
                        'uri'        => $uri,
                        'action'     => $action,
                        'simply_action'     => $simply_action,
                        'middleware' => implode(PHP_EOL, array_unique($middlewares)),
                    ];
                }
            }
        }

        foreach ($regRouters as $method => $items) {
            foreach ($items as $item) {
                foreach ($item['routeMap'] as $route_item) {
                    $handle = array_shift($route_item);
                    $uri    = $handle->route;
                    $this->info($uri);
                    $serverName = 'http';

                    if (is_array($handle->callback)) {
                        $action = $handle->callback[0] . '::' . $handle->callback[1];
                    } else {
                        $action = $handle->callback;
                    }
                    $middlewares = MiddlewareManager::get('http', $uri, $method);
                    if (in_array(AdminPermissionMiddleware::class, $middlewares)) {
                        preg_match("/App\\\Controller\\\Admin\\\([a-zA-Z]+)Controller@([a-zA-Z]+)/", $action, $matchs);
                        if (count($matchs) !== 3) {
                            $this->error("the acttion {$action} not match");
                            continue;
                        }
                        $simply_action = $matchs[1] . "@" . $matchs[2];
                        Permission::firstOrCreate([
                            'action' => $simply_action,
                        ], ['name' => $simply_action]);
                        $data[$method . '.' . $uri] = [
                            'server'     => $serverName,
                            'method'     => $method,
                            'uri'        => $uri,
                            'action'     => $action,
                            'simply_action'     => $simply_action,
                            'middleware' => implode(PHP_EOL, array_unique($middlewares)),
                        ];
                    }
                }
            }
        }

        $this->table(['Server', 'Method', 'Uri', 'Action', 'SimplyAction', 'Mideelewares'], $data);
        $this->info('success.');
    }
}
