<?php

declare(strict_types=1);

if (!function_exists('auth')) {
    function auth(?string $guard = '')
    {
        if (!\Hyperf\Utils\Context::has(\App\Lib\Auth\AuthGuard::class)) {
            \Hyperf\Utils\Context::set(\App\Lib\Auth\AuthGuard::class, new \App\Lib\Auth\AuthGuard());
        }
        return \Hyperf\Utils\Context::get(\App\Lib\Auth\AuthGuard::class)->setGuard($guard);
    }
}
