<?php

declare(strict_types=1);

return [
    'models' => [
        'permission' => \App\Lib\Permission\Model\Permission::class,
        'role'       => \App\Model\Role::class,
    ],
    'cache'  => [
        'expiration_time' => DateInterval::createFromDateString('1 weeks'),
        'key'             => 'permission.cache',
        'model_key'       => 'name',
    ]
];
