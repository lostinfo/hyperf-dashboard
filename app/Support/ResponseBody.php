<?php

declare(strict_types=1);

namespace App\Support;


use Hyperf\HttpMessage\Stream\SwooleStream;

class ResponseBody
{
    /**
     * @param string $message
     * @param array $data
     * @return SwooleStream
     */
    public static function createBody(string $message, array $data = []): SwooleStream
    {
        $json = [
            'message'  => $message,
            'data' => $data,
        ];
        return new SwooleStream(json_encode($json));
    }

}