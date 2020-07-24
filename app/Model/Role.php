<?php

declare(strict_types=1);

namespace App\Model;

use App\Lib\Permission\Contracts\Role as RoleContract;

class Role extends \App\Lib\Permission\Model\Role
{
    protected $casts = [
        'menus' => 'array',
    ];

    public static function findOrCreate(string $name, $guardName, $attributes = []): RoleContract
    {
        $role = static::where(['name' => $name, 'guard_name' => $guardName])->first();

        if (!$role) {
            $role             = new Role();
            $role->name       = $name;
            $role->guard_name = $guardName;
            $role->menus      = [];
            $role->save();
        }

        return $role;
    }

    public static function getMenusViaRoles($roles)
    {
        if (empty($roles)) {
            return [];
        }
        return collect($roles)->flatMap(function ($role) {
            return $role->menus;
        })->flatten()->sort()->values()->toArray();
    }
}
