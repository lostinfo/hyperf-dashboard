<?php

declare(strict_types=1);

namespace App\Model;

class Role extends Model
{
    protected $casts = [
        'menus' => 'array',
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }
}