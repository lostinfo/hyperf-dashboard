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

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name'                   => env('APP_NAME', 'skeleton'),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    'app_url'                    => env('APP_URL', 'http://localhost'),
    'jwt_key'                    => env('JWT_SECRET', '123456'),
    'menus'                      => [
        [
            'group_name' => '系统',
            'menus'      => [
                [
                    'path'     => '/admin/index',
                    'name'     => '主页',
                    'icon'     => 'fa fa-home',
                    'unfolded' => false
                ],
                [
                    'name'         => '机构管理',
                    'icon'         => 'fa fa-group',
                    'unfolded'     => true,
                    'children'     => [
                        [
                            'path' => '/admin/companies',
                            'name' => '公司列表',
                        ],
                        [
                            'path' => '/admin/agencies',
                            'name' => '机构列表',
                        ],
                        [
                            'path' => '/admin/sales',
                            'name' => '营业部列表',
                        ],
                    ]
                ],
                [
                    'path'     => '/admin/users',
                    'name'     => '用户管理',
                    'icon'     => 'fa fa-user',
                    'unfolded' => false
                ],
                [
                    'path'     => '/admin/articles',
                    'name'     => '文章管理',
                    'icon'     => 'fa fa-file-text',
                    'unfolded' => false
                ],
            ]
        ],
        [
            'group_name' => '设置',
            'pages'      => [
                '/admin/role/:id',
                '/admin/admin/:id',
                '/admin/swiper/:id',
            ],
            'menus'      => [
                [
                    'name'         => '权限管理',
                    'icon'         => 'fa fa-shield',
                    'unfolded'     => true,
                    'supper_admin' => true,
                    'children'     => [
                        [
                            'path' => '/admin/roles',
                            'name' => '角色列表',
                        ],
                        [
                            'path' => '/admin/permissions',
                            'name' => '权限列表',
                        ],
                        [
                            'path' => '/admin/admins',
                            'name' => '管理员列表',
                        ],
                    ]
                ],
            ]
        ],
    ]
];
