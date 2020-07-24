<?php

declare(strict_types=1);

namespace App\Lib\Auth;


use App\Lib\Auth\Exceptions\AuthException;
use Hyperf\Database\Model\Model;

class AuthGuard
{
    /**
     * @var string
     */
    protected $guard;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var Authenticatable
     */
    protected $user;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var array
     */
    protected $config;

    public function __construct()
    {
        $this->config = config('auth');
    }

    public function setGuard(?string $guard)
    {
        $guard = $guard ?: array_key_first($this->config['guards']);
        if (!in_array($guard, array_keys($this->config['guards']))) {
            throw new AuthException("guard not defind.");
        };
        if ($this->guard !== null && $this->guard !== $guard) {
            throw new AuthException("There guard must be equal to {$this->guard}");
        }
        $this->guard = $guard;
        $this->setModelByGuard($this->guard);
        return $this;
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        $id_key = $user->getAuthIdentifier();
        $this->setId($user->$id_key);

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function id()
    {
        if ($this->id === null) {
            throw new AuthException("Unauthorized.");
        }
        return $this->id;
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }
        if ($this->id === null) {
            throw new AuthException("Unauthorized.");
        }
        $this->user = make($this->model)->find($this->id);
        if (empty($this->user)) {
            throw new AuthException("user not exists.");
        }
        return $this->user;
    }

    protected function setModelByGuard($guard_name)
    {
        foreach ($this->config['guards'] as $guard => $config) {
            if ($guard === $guard_name) {
                $this->model = $config['model'];
            }
        }
        if ($this->model === null) {
            throw new AuthException("model not defind in auth config.");
        }
    }
}
