<?php

declare(strict_types=1);

namespace App\Exception\Handler;


use App\Lib\Permission\Exceptions\PermissionException;
use App\Support\ResponseHelper;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PermissionExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        return $response->withStatus(ResponseHelper::HTTP_UNPROCESSABLE_ENTITY)->withBody(ResponseHelper::createBody($throwable->getMessage()));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof PermissionException;
    }
}
