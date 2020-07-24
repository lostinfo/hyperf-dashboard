<?php

declare(strict_types=1);

namespace App\Lib;

use Hyperf\Utils\ApplicationContext;

class Log
{
    /**
     * @param string $name
     * @return mixed
     */
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }
}
