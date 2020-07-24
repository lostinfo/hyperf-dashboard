<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
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
                if ($guard_name = $request->input('guard_name')) {
                    $query->where(['guard_name' => $guard_name]);
                }
            })
                ->withCount('permissions')
                ->orderBy($this->getOrderByColumn(), $this->getOrderByDirection())
                ->paginate($this->getPageSize())
                ->toArray()
        );
    }

    public function store(RoleStoreRequest $request)
    {
        $validated = $request->validated();

        if ($id = $request->input('id')) {
            $role = Role::findOrFail($id);
        } else {
            $role             = new Role();
            $role->guard_name = $validated['guard_name'];
        }
        $role->name  = $validated['name'];
        $role->menus = $validated['menus'];
        $role->save();
        $role->syncPermissions($validated['permissions']);

        return $this->response;
    }

    public function info($id)
    {
        $role = Role::findOrFail($id);
        $role->load(['permissions']);
        $permission_ids      = collect($role->permissions)->pluck('id')->all();
        $role                = $role->toArray();
        $role['permissions'] = $permission_ids;
        return $this->response->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $this->response;
    }

    public function options()
    {
        $request = $this->request;
        return $this->response->json(
            Role::where(function ($query) use ($request) {
                if ($guard_name = $request->input('guard_name')) {
                    $query->where(['guard_name' => $guard_name]);
                }
            })
                ->select(['id', 'name'])->get()->toArray()
        );
    }

    public function menuOptions()
    {
        $guard_name = $this->request->input('guard_name');
        return $this->response->json(config('auth.menus.' . $guard_name));
    }
}
