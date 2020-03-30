<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\Admin;
use App\Request\Admin\AdminStoreRequest;
use App\Support\ResponseHelper;

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
            })->with(['roles'])
                ->orderBy(
                    $request->input('order_by_column', 'id'),
                    $request->input('order_by_direction', 'desc')
                )->paginate((int)($request->input('page_size', 15)))
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
        $admin->roles()->sync($validated['roles']);

        return $this->response;
    }

    public function info($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->load(['roles']);

        $role_ids       = collect($admin->roles)->pluck('id')->all();
        $admin          = $admin->toArray();
        $admin['roles'] = $role_ids;

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
