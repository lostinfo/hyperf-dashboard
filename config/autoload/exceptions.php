<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'handler' => [
        'http' => [
            \App\Exception\Handler\ModelNotFoundExceptionHandler::class,
            \App\Exception\Handler\ValidationExceptionHandler::class,
            \App\Exception\Handler\AuthExceptionHandler::class,
            \App\Exception\Handler\PermissionExceptionHandler::class,
            Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
