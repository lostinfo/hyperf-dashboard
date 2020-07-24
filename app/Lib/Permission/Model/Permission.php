<?php

declare(strict_types=1);

namespace App\Lib\Permission\Model;


use App\Lib\Permission\Contracts\Permission as PermissionContract;
use App\Lib\Permission\Exceptions\PermissionException;
use App\Lib\Permission\PermissionRegister;
use App\Lib\Permission\Traits\HasRoles;
use App\Model\Role;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Collection;

class Permission extends Model implements PermissionContract
{
    use HasRoles;

    protected $fillable = ['name', 'alias', 'guard_name'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
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

    /**
     * @param string $name
     * @param $guardName
     * @return PermissionContract
     * @throws PermissionException
     */
    public static function findByName(string $name, $guardName): PermissionContract
    {
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();
        if (!$permission) {
            throw new PermissionException("There is no permission named `{$name}` for guard `{$guardName}`.");
        }
        return $permission;
    }

    /**
     * @param int $id
     * @param $guardName
     * @return PermissionContract
     * @throws PermissionException
     */
    public static function findById(int $id, $guardName): PermissionContract
    {
        $permission = static::getPermissions(['id' => $id, 'guard_name' => $guardName])->first();

        if (!$permission) {
            throw new PermissionException("There is no [permission] with id `{$id}`.");
        }

        return $permission;
    }

    /**
     * @param string $name
     * @param $guardName
     * @return PermissionContract
     */
    public static function findOrCreate(string $name, $guardName): PermissionContract
    {
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();

        if (!$permission) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }


    /**
     * @param array $params
     * @return Collection
     */
    protected static function getPermissions(array $params = []): Collection
    {
        return make(PermissionRegister::class)->getPermissions($params);
    }
}
