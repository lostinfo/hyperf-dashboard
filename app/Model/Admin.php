<?php

declare(strict_types=1);

namespace App\Model;


class Admin extends Model
{
    protected $casts = [
        'is_supper_admin' => 'bool',
        'active' => 'bool',
    ];

    protected $hidden = [
        'password'
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'admin_has_roles'
        );
    }

    public function hasPermission(string $action)
    {
        $permission = Permission::where(['action' => $action])->first();
        if (empty($permission)) return false;

        $admin = $this->getModel();
        if (!$admin->exists) return false;

        foreach ($admin->roles as $role) {
            if (AdminHasRole::where(['role_id' => $role->id, 'admin_id' => $admin->id])->exists()) {
                return true;
            }
        }
        return false;
    }
}
