<?php

declare(strict_types=1);

namespace App\Lib\Permission\Traits;


use App\Lib\Permission\Exceptions\PermissionException;
use App\Lib\Permission\Contracts\Role;
use App\Lib\Permission\PermissionRegister;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hyperf\Utils\Collection;

trait HasRoles
{
    use HasPermissions;

    public static function detachRoles($model)
    {
        $model->roles()->detach();
    }

    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }

    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';
            $guard  = $guard ?: $this->getDefaultGuardName();

            return Role::{$method}($role, $guard);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere('roles.id', $role->id);
                }
            });
        });
    }

    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->roles()->sync($roles, false);
            $model->load('roles');
        } else {
            throw new PermissionException("There model not exists");
        }

        $this->forgetCachedPermissions();

        return $this;
    }

    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));

        $this->load('roles');

        return $this;
    }

    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    public function hasRole($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_int($roles)) {
            return $this->roles->contains('id', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->getRoleNames()) == $roles;
    }

    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter   = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (!in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }

    protected function getRoleClass()
    {
        return make(PermissionRegister::class)->getRoleClass();
    }
}
