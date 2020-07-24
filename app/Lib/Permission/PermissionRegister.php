<?php

declare(strict_types=1);

namespace App\Lib\Permission;


use App\Lib\Permission\Contracts\Permission;
use App\Lib\Permission\Contracts\Role;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class PermissionRegister
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $permissionClass;

    /**
     * @var string
     */
    protected $roleClass;

    /**
     * @var Collection
     */
    protected $permissions;

    /**
     * @var \DateInterval|int
     */
    public static $cacheExpirationTime;

    /**
     * @var string
     */
    public static $cacheKey;

    /**
     * @var string
     */
    public static $cacheModelKey;

    public function __construct(ContainerInterface $container, CacheInterface $cache)
    {
        $this->container = $container;
        $this->cache     = $cache;

        $this->permissionClass = config('permission.models.permission');
        $this->roleClass       = config('permission.models.role');

        self::$cacheExpirationTime = config('permission.cache.expiration_time');
        self::$cacheKey            = config('permission.cache.key');
        self::$cacheModelKey       = config('permission.cache.model_key');
    }

    public function forgetCachedPermissions(): bool
    {
        $this->permissions = null;

        return $this->cache->delete(self::$cacheKey);
    }

    public function getPermissions(array $params = []): Collection
    {
        if ($this->permissions === null) {
            if ($this->cache->has(self::$cacheKey)) {
                $this->permissions = $this->cache->get(self::$cacheKey);
            } else {
                $this->permissions = $this->getPermissionClass()
                    ->with('roles')
                    ->get();
                $this->cache->set(self::$cacheKey, $this->permissions, self::$cacheExpirationTime);
            }
        }

        $permissions = clone $this->permissions;

        foreach ($params as $attr => $value) {
            $permissions = $permissions->where($attr, $value);
        }

        return $permissions;
    }

    public function getPermissionClass(): Permission
    {
        return $this->container->get($this->permissionClass);
    }

    public function getRoleClass(): Role
    {
        return $this->container->get($this->roleClass);
    }
}
