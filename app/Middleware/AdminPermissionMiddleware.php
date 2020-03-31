<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Lib\Log;
use App\Services\RoleService;
use App\Support\ResponseBody;
use App\Support\ResponseHelper;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminPermissionMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpResponse
     */
    protected $response;


    public function __construct(ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $admin = Context::get('admin');
        if (empty($admin)) {
            return $this->response->withStatus(ResponseHelper::HTTP_UNAUTHORIZED);
        }
        if (!$admin->is_supper_admin) {
            $dispatcher = $request->getAttribute('Hyperf\HttpServer\Router\Dispatched');
            if (is_array($dispatcher->handler->callback)) {
                $action = $dispatcher->handler->callback[0] . '::' . $dispatcher->handler->callback[1];
            } else {
                $action = $dispatcher->handler->callback;
            }
            preg_match("/App\\\Controller\\\Admin\\\([a-zA-Z]+)Controller@([a-zA-Z]+)/", $action, $matchs);
            if (count($matchs) !== 3) {
                return $this->response->withStatus(ResponseHelper::HTTP_UNPROCESSABLE_ENTITY)->withBody(ResponseBody::createBody("未匹配到权限"));
            }
            $simply_action = $matchs[1] . "@" . $matchs[2];
            $roleService = make(RoleService::class);
            if (!$roleService->hasPermission($admin, $simply_action)) {
                return $this->response->withStatus(ResponseHelper::HTTP_UNPROCESSABLE_ENTITY)->withBody(ResponseBody::createBody("您没有 {$simply_action} 访问权限"));
            }
        }

        return $handler->handle($request);
    }
}
