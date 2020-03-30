<?php


namespace App\Exception\Handler;


use App\Support\ResponseHelper;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        $errors = $throwable->validator->errors()->getMessages();
        return $response->withStatus(ResponseHelper::HTTP_UNPROCESSABLE_ENTITY)->withBody(ResponseHelper::createBody('给定的数据无效', [], $errors));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
