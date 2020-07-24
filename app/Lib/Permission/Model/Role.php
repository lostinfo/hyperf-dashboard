<?php

declare(strict_types=1);

namespace App\Lib\Permission\Model;


use App\Lib\Permission\Contracts\Role as RoleContract;
use App\Lib\Permission\Exceptions\PermissionException;
use App\Lib\Permission\PermissionRegister;
use App\Lib\Permission\Traits\HasPermissions;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsToMany;

class Role extends Model implements RoleContract
{
    use HasPermissions;

    protected $fillable = ['name', 'guard_name'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    public function saved(Saved $event)
    {
        make(PermissionRegister::class)->forgetCachedPermissions();
    }

    public function deleted(Deleted $event)
    {
        make(PermissionRegister::class)->forgetCachedPermissions();
    }

    public function deleting(Deleted $event)
    {
        static::detachPermissions($this);
    }

    /**
     * @param string $name
     * @param $guardName
     * @return RoleContract
     * @throws PermissionException
     */
    public static function findByName(string $name, $guardName): RoleContract
    {
        $role = static::where(['name' => $name, 'guard_name' => $guardName])->first();
        if (!$role) {
            throw new PermissionException("There is no role named `{$name}`.");
        }

        return $role;
    }

    /**
     * @param int $id
     * @param $guardName
     * @return RoleContract
     * @throws PermissionException
     */
    public static function findById(int $id, $guardName): RoleContract
    {
        $role = static::where(['id' => $id, 'guard_name' => $guardName])->first();
        if (!$role) {
            throw new PermissionException("There is no role with id `{$id}`.");
        }

        return $role;
    }

    /**
     * @param string $name
     * @param $guardName
     * @return RoleContract
     */
    public static function findOrCreate(string $name, $guardName): RoleContract
    {
        $role = static::where(['name' => $name, 'guard_name' => $guardName])->first();

        if (!$role) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }

    /**
     * @param \App\Lib\Permission\Contracts\Permission|string $permission
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission, $this->guard_name);
        }
        if (is_int($permission)) {
            $permission = Permission::findById($permission, $this->guard_name);
        }

        return $this->permissions->contains('id', $permission->id);
    }
}
