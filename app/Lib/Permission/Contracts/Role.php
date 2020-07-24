<?php

declare(strict_types=1);

namespace App\Lib\Permission\Contracts;


use Hyperf\Database\Model\Relations\BelongsToMany;

interface Role
{
    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany;

    /**
     * @param string $name
     * @param $guardName
     * @return Role
     */
    public static function findByName(string $name, $guardName): self;

    /**
     * @param int $id
     * @param $guardName
     * @return Role
     */
    public static function findById(int $id, $guardName): self;

    /**
     * @param string $name
     * @param $guardName
     * @return Role
     */
    public static function findOrCreate(string $name, $guardName): self;

    /**
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermissionTo($permission): bool;

}
