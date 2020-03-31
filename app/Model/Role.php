<?php

declare(strict_types=1);

namespace App\Model;

use App\Services\RoleService;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Updated;

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

    public function updated(Updated $event)
    {
        $roleService = make(RoleService::class);
        $roleService->cleanCache($this->id);
    }

    public function deleted(Deleted $event)
    {
        $roleService = make(RoleService::class);
        $roleService->cleanCache($this->id);
    }
}
