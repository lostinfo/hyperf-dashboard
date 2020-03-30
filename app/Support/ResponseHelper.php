<?php

declare(strict_types=1);

namespace App\Support;


use Hyperf\HttpMessage\Stream\SwooleStream;

class ResponseHelper
{

    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_UNPROCESSABLE_ENTITY = 422;

    /**
     * @param string $message
     * @param array $data
     * @param array $errors
     * @return SwooleStream
     */
    public static function createBody(string $message, array $data = [], array $errors): SwooleStream
    {
        $json = [
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ];
        return new SwooleStream(json_encode($json, JSON_UNESCAPED_UNICODE));
    }

}
