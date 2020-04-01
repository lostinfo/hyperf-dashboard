<?php

declare(strict_types=1);

namespace App\Services;


use App\Model\Admin;
use App\Model\Role;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;

class RoleService
{
    /**
     * @param Role[] $roles
     * @return array
     */
    public function getPermissionsViaRoles($roles)
    {
        if (empty($roles)) {
            return [];
        }
        return collect($roles)->flatMap(function ($role) {
            return $this->getPermissionsViaRole($role->id);
        })->flatten()->unique()->sort()->values()->toArray();
    }

    /**
     * @param $id
     * @Cacheable(prefix="rolePermissions", ttl=86400, listener="role-change")
     * @return array
     */
    public function getPermissionsViaRole($id)
    {
        $role = Role::find($id);
        if ($role) {
            return $role->permissions->pluck('action')->toArray();
        }
        return [];
    }

    /**
     * @param Role[] $roles
     * @return array
     */
    public function getMenusViaRoles($roles)
    {
        if (empty($roles)) {
            return [];
        }
        return collect($roles)->flatMap(function ($role) {
            return $role->menus;
        })->flatten()->sort()->values()->toArray();
    }

    /**
     * @param Admin $admin
     * @param $permission
     * @return bool
     */
    public function hasPermission(Admin $admin, $permission)
    {
        return in_array($permission, $this->getPermissionsViaRoles($admin->roles));
    }

    /**
     * @param $id
     */
    public function cleanCache($id)
    {
        $container = ApplicationContext::getContainer();
        $container->get(EventDispatcherInterface::class)->dispatch(new DeleteListenerEvent('role-change', [$id]));
    }
}
