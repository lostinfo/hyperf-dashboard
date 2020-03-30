<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Admin;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class AdminEditCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('admin:edit');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('username', InputArgument::REQUIRED, '用户名');
        $this->addArgument('password', InputArgument::REQUIRED, '密码');
        $this->addArgument('supper-admin', InputArgument::OPTIONAL, '是否超级管理员', 0);
        $this->setHelp('admin:edit {username} {password} {supper-admin}');
        $this->setDescription('admin edit');
    }

    public function handle()
    {
        $username     = $this->input->getArgument('username');
        $password     = $this->input->getArgument('password');
        $supper_admin = $this->input->getArgument('supper-admin');

        $admin = Admin::where(['username' => $username])->first();

        if (empty($admin)) {
            $admin = new Admin();
        }

        $admin->username        = $username;
        $admin->password        = password_hash($password, PASSWORD_BCRYPT);
        $admin->is_supper_admin = (boolean)$supper_admin;
        $admin->save();

        $this->table(['Username', 'Password', 'isSupperAdmin'], [
            [
                'username'        => $username,
                'password'        => $password,
                'is_supper_admin' => $supper_admin ? 'yes' : 'no',
            ]
        ]);
    }
}
