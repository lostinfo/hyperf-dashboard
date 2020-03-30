<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\AdminHasRole;
use App\Model\Role;
use App\Request\Admin\RoleStoreRequest;

class RoleController extends AbstractController
{
    public function index()
    {
        $request = $this->request;
        return $this->response->json(
            Role::where(function ($query) use ($request) {
                if ($name = $request->input('name')) {
                    $query->where(['name' => $name]);
                }
            })->withCount('permissions')
                ->orderBy(
                $request->input('order_by_column', 'id'),
                $request->input('order_by_direction', 'desc')
            )->paginate((int)($request->input('page_size', 15)))
                ->toArray()
        );
    }

    public function store(RoleStoreRequest $request)
    {
        $validated = $request->validated();

        if ($id = $request->input('id')) {
            $role = Role::findOrFail($id);
        } else {
            $role = new Role();
        }
        $role->name  = $validated['name'];
        $role->menus = $validated['menus'];
        $role->save();
        $role->permissions()->sync($validated['permissions']);

        return $this->response;
    }

    public function info($id)
    {
        $role = Role::findOrFail($id);
        $role->load(['permissions']);
        $permission_ids = collect($role->permissions)->pluck('id')->all();
        $role = $role->toArray();
        $role['permissions'] = $permission_ids;
        return $this->response->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if (AdminHasRole::where(['role_id' => $role->id])->exists()) {
            return $this->responseMsg('此角色使用中，不可删除');
        }
        $role->delete();
        return $this->response;
    }

    public function options()
    {
        return $this->response->json(Role::select(['id', 'name'])->get()->toArray());
    }

    public function menuOptions()
    {
        return $this->response->json(config('menus'));
    }
}
