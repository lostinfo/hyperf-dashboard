<?php

declare(strict_types=1);

namespace App\Lib\Permission\Contracts;


use Hyperf\Database\Model\Relations\BelongsToMany;

interface Permission
{
    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * @param string $name
     * @param $guardName
     * @return Permission
     */
    public static function findByName(string $name, $guardName): self;

    /**
     * @param int $id
     * @param $guardName
     * @return Permission
     */
    public static function findById(int $id, $guardName): self;

    /**
     * @param string $name
     * @param $guardName
     * @return Permission
     */
    public static function findOrCreate(string $name, $guardName): self;
}
