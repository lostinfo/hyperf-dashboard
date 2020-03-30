<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Lib\Jwt;
use App\Model\Admin;
use App\Request\Admin\AdminLoginRequest;

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

        $jwt        = new Jwt();
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
        $token = $token[0];
        $token = substr($token, 7);
        $jwt = new Jwt();
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

    protected function responseAdmin(Admin $admin)
    {
        if (!$admin->is_supper_admin) {
            // todo get paths by admin roles
            $menus = $this->getMenusByPaths([]);
        } else {
            $menus = config('menus');
        }

        // todo menus permissions
        return [
            'id'              => $admin->id,
            'username'        => $admin->username,
            'is_supper_admin' => $admin->is_supper_admin,
            'menus'           => $menus,
            'permissions'     => [],
            'created_at'      => $admin->created_at,
        ];
    }

    protected function getMenusByPaths(array $paths)
    {
        $menus = config('menus');
        foreach ($menus as $menu_group_index => $menu_group) {
            foreach ($menu_group['menus'] as $menu_index => $menu) {
                if ($menu['unfolded']) {
                    foreach ($menu['children'] as $menu_item_index => $menu_item) {
                        if (!in_array($menu_item['path'], $paths)) {
                            unset($menus[$menu_group_index]['menus'][$menu_index]['children'][$menu_item_index]);
                        }
                    }
                    if (count($menus[$menu_group_index]['menus'][$menu_index]['children']) < 1) {
                        unset($menus[$menu_group_index]['menus'][$menu_index]);
                    }
                } else {
                    if (!in_array($menu['path'], $paths)) {
                        unset($menus[$menu_group_index]['menus'][$menu_index]);
                    }
                }
            }
            if (count($menus[$menu_group_index]['menus']) < 1) {
                unset($menus[$menu_group_index]);
            }
        }
        return array_values($menus);
    }
}
