<?php

declare(strict_types=1);

namespace App\Model;


class Permission extends Model
{
    protected $fillable = [
        'action', 'name',
    ];

    public function rules()
    {
        return $this->belongsToMany(
            Role::class,
            RoleHasPermission::class,
            'permission_id',
            'role_id'
        );
    }
}
