<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Lib\Jwt;
use App\Support\ResponseHelper;
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
            return $this->response->withStatus(ResponseHelper::HTTP_UNAUTHORIZED)->withBody(ResponseHelper::createBody('Unauthorized.'));
        }
        $token = $token[0];
        $token = substr($token, 7);
        $jwt_id = $this->jwt->validateToken($token, 'admin');
        if (!$jwt_id) {
            return $this->response->withStatus(ResponseHelper::HTTP_UNAUTHORIZED)->withBody(ResponseHelper::createBody('Unauthorized.'));
        }
        auth('admin')->setId($jwt_id);
        return $handler->handle($request);
    }
}
