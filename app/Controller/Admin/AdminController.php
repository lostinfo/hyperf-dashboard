<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\Admin;
use App\Request\Admin\AdminStoreRequest;

class AdminController extends AbstractController
{
    public function index()
    {
        $request = $this->request;
        return $this->response->json(
            Admin::where(function ($query) use ($request) {
                if ($username = $request->input('username')) {
                    $query->where(['username' => $username]);
                }
            })
                ->with(['roles'])
                ->orderBy($this->getOrderByColumn(), $this->getOrderByDirection())
                ->paginate($this->getPageSize())
                ->toArray()
        );
    }

    public function store(AdminStoreRequest $request)
    {
        $validated = $request->validated();

        if ($id = $request->input('id')) {
            $admin = Admin::where(['is_supper_admin' => false])->findOrFail($id);
            if ($validated['password']) {
                $admin->password = password_hash($validated['password'], PASSWORD_BCRYPT);
            }
        } else {
            $admin           = new Admin();
            $admin->username = $validated['username'];
            $admin->password = password_hash($validated['password'], PASSWORD_BCRYPT);
        }
        $admin->is_supper_admin = false;
        $admin->active          = $validated['active'];
        $admin->save();
        $admin->syncRoles($validated['roles']);
        $admin->syncPermissions($validated['permissions']);

        return $this->response;
    }

    public function info($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->load(['roles', 'permissions']);

        $role_ids             = collect($admin->roles)->pluck('id')->all();
        $permission_ids       = collect($admin->permissions)->pluck('id')->all();
        $admin                = $admin->toArray();
        $admin['roles']       = $role_ids;
        $admin['permissions'] = $permission_ids;

        return $this->response->json($admin);
    }

    public function destroy($id)
    {
        return $this->responseMsg('不可删除');
        // todo can destory
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return $this->response;
    }
}
