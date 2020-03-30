<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Lib\Jwt;
use App\Model\Admin;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var Jwt
     */
    protected $jwt;

    public function __construct(ContainerInterface $container, HttpResponse $response, Jwt $jwt)
    {
        $this->container = $container;
        $this->response = $response;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeader('Authorization');
        if (empty($token)) {
            return $this->response->json(['status' => false]);
        }
        $token = $token[0];
        $token = substr($token, 7);
        $jwt_id = $this->jwt->validateToken($token, 'admin');
        if (!$jwt_id) {
            return $this->response->withStatus(401);
        }
        $admin = Admin::findOrFail($jwt_id);
        Context::set('admin', $admin);
        return $handler->handle($request);
    }
}
