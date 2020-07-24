<?php

declare(strict_types=1);

namespace App\Exception\Handler;


use App\Lib\Auth\Exceptions\AuthException;
use App\Support\ResponseHelper;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        return $response->withStatus(ResponseHelper::HTTP_UNAUTHORIZED)->withBody(ResponseHelper::createBody($throwable->getMessage()));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof AuthException;
    }
}
