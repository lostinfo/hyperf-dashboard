<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Lib\Jwt;
use App\Model\Admin;
use App\Model\Role;
use App\Request\Admin\AdminLoginRequest;
use App\Support\Helper;

class AuthController extends AbstractController
{
    public function login(AdminLoginRequest $request)
    {
        $validated = $request->validated();

        $admin = Admin::where(['username' => $validated['username']])->first();

        if (empty($admin)) {
            return $this->responseMsg('账户不存在');
        }

        if (!password_verify($validated['password'], $admin->password)) {
            return $this->responseMsg('密码错误');
        }

        $jwt   = new Jwt();
        $token = $jwt->login('admin', $admin->id);
        if (!$token) {
            return $this->responseMsg('登陆失败');
        }

        return $this->response->json([
            'authorization' => $token,
            'admin'         => $this->responseAdmin($admin),
        ]);

    }


    public function check()
    {
        $token = $this->request->getHeader('Authorization');
        if (empty($token)) {
            return $this->response->json(['status' => false]);
        }
        $token  = $token[0];
        $token  = substr($token, 7);
        $jwt    = new Jwt();
        $jwt_id = $jwt->validateToken($token, 'admin');
        if (!$jwt_id) {
            return $this->response->json(['status' => false]);
        }

        $admin = Admin::findOrFail($jwt_id);

        return $this->response->json([
            'status' => true,
            'admin'  => $this->responseAdmin($admin),
        ]);
    }

    public function guardOptions()
    {
        return $this->response->json(array_keys(config('auth.guards')));
    }

    protected function responseAdmin(Admin $admin)
    {
        $admin->load(['roles']);
        if (!$admin->is_supper_admin) {
            $admin_paths = Role::getMenusViaRoles($admin->roles);
            $menus       = $this->getMenusByPaths($admin_paths);
            $permissions = $admin->getAllPermissions()->pluck('name')->toArray();
        } else {
            $menus       = config('auth.menus.admin');
            $permissions = [];
        }

        return [
            'id'              => $admin->id,
            'username'        => $admin->username,
            'is_supper_admin' => $admin->is_supper_admin,
            'roles'           => $admin->roles->only(['id', 'name'])->toArray(),
            'menus'           => $menus,
            'permissions'     => $permissions,
            'created_at'      => $admin->created_at->format('Y-m-d'),
        ];
    }

    protected function getMenusByPaths(array $paths)
    {
        $menus = config('auth.menus.admin');
        return Helper::filterMenusByPaths($menus, $paths);
    }
}
