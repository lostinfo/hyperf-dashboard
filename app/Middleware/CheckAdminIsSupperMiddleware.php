<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\ResponseHelper;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckAdminIsSupperMiddleware implements MiddlewareInterface
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
            return $this->response->withStatus(401);
        }
        if (!$admin->is_supper_admin) {
            return $this->response->withStatus(ResponseHelper::HTTP_FORBIDDEN)->withBody(ResponseHelper::createBody('访问被拒绝'));
        }

        return $handler->handle($request);
    }
}
