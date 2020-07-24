<?php

declare(strict_types=1);

namespace App\Lib\Permission\Commands;


use App\Lib\Permission\PermissionRegister;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class CacheResetCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('permission:cache-reset');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Reset the permission cache');
    }

    public function handle()
    {
        make(PermissionRegister::class)->forgetCachedPermissions();

        $this->info('Permission cache flushed.');
    }
}
