<?php

declare(strict_types=1);

namespace App\Lib\Permission\Traits;


use App\Lib\Permission\Exceptions\PermissionException;
use App\Lib\Permission\Guard;
use App\Lib\Permission\Contracts\Permission;
use App\Lib\Permission\PermissionRegister;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hyperf\Database\Model\Builder;
use Hyperf\Utils\Collection;

trait HasPermissions
{
    public static function detachPermissions($model)
    {
        $model->permissions()->detach();
    }

    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            'model_has_permissions',
            'model_id',
            'permission_id'
        );
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param Builder $query
     * @param $permissions
     * @return Builder
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_replace($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function ($query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                $query->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere('permission.id', $permission->id);
                    }
                });
            });
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->where(function ($query) use ($rolesWithPermissions) {
                        foreach ($rolesWithPermissions as $role) {
                            $query->orWhere('roles.id', $role->id);
                        }
                    });
                });
            }
        });
    }

    /**
     * @param $permissions
     * @return array
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return array_map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission;
            }
            return $this->getPermissionClass()->findByName($permission, $this->getDefaultGuardName());
        }, $permissions);
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (!$permission instanceof Permission) {
            $permission = (string)$permission;
            throw new PermissionException("There is no permission named `{$permission}` for guard `{$this->getDefaultGuardName()}`.");
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    public function hasUncachedPermissionTo($permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    public function checkPermissionTo($permission): bool
    {
        try {
            return $this->hasPermissionTo($permission);
        } catch (PermissionException $e) {
            return false;
        }
    }

    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permissions)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if (!$this->checkPermissionTo($permissions)) {
                return false;
            }
        }

        return true;
    }

    protected function hasPermissionViaRole(Permission $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    public function hasDirectPermission($permission): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission);
            if (!$permission) {
                return false;
            }
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission);
            if (!$permission) {
                return false;
            }
        }

        if (!$permission instanceof Permission) {
            return false;
        }

        return $this->permissions->contains('id', $permission->id);
    }

    public function getPermissionsViaRoles(): Collection
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $model->load('permissions');
        } else {
            throw new PermissionException("There model not exists");
        }

        $this->forgetCachedPermissions();

        return true;
    }

    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        $this->load('permissions');

        return $this;
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions()->pluck('name');
    }

    protected function getStoredPermission($permissions)
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permissions)) {
            return $permissionClass->findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_int($permissions)) {
            return $permissionClass->findById($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return $permissionClass
                ->whereIn('name', $permissions)
                ->where(['guard_name' => $this->getDefaultGuardName()])
                ->get();
        }

        return $permissions;
    }

    public function forgetCachedPermissions()
    {
        make(PermissionRegister::class)->forgetCachedPermissions();
    }

    protected function ensureModelSharesGuard($roleOrPermission)
    {
        $guardNames = $this->getGuardNames();
        if (!$guardNames->contains($roleOrPermission->guard_name)) {
            throw new PermissionException("The given role or permission should use guard `{$guardNames->implode(', ')}` instead of `{$roleOrPermission->guard_name}`.");
        }
    }

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * Get an instance of the permission class.
     *
     * @return Permission
     */
    protected function getPermissionClass()
    {
        return make(PermissionRegister::class)->getPermissionClass();
    }
}
