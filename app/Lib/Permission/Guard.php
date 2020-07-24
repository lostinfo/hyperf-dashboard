<?php

declare(strict_types=1);

namespace App\Lib\Permission;


use Hyperf\Utils\Collection;

class Guard
{
    public static function getNames($model): Collection
    {
        if (is_object($model)) {
            $guard_name = $model->guard_name ?? null;
        }

        if (!isset($guard_name)) {
            $class = is_object($model) ? get_class($model) : $model;

            $guard_name = (new \ReflectionClass($class))->getDefaultProperties()['guard_name'] ?? null;
        }

        if ($guard_name) {
            return collect($guard_name);
        }

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                return $guard['model'];
            })->filter(function ($model) use ($class) {
                return $class === $model;
            })
            ->keys();
    }

    public static function getDefaultName($class): string
    {
        $default = array_keys(config('auth.guards'))[0];

        return static::getNames($class)->first() ?: $default;
    }
}
