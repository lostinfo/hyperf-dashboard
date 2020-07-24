<?php

declare(strict_types=1);

namespace App\Model;


use App\Lib\Auth\Authenticatable;
use App\Lib\Permission\Traits\HasRoles;
use Hyperf\Database\Model\Events\Deleting;

class Admin extends Model implements Authenticatable
{
    use HasRoles;

    protected $guard_name = 'admin';

    protected $casts = [
        'is_supper_admin' => 'bool',
        'active'          => 'bool',
    ];

    protected $hidden = [
        'password'
    ];

    public function deleting(Deleting $event)
    {
        static::detachPermissions($this);
        static::detachRoles($this);
    }

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function getAuthIdentifier()
    {
        return 'id';
    }
}
