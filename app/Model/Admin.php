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
}
