<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Lib\Permission\Model\Permission;
use App\Request\Admin\PermissionStoreRequest;

class PermissionController extends AbstractController
{
    public function index()
    {
        $request = $this->request;
        return $this->response->json(
            Permission::where(function ($query) use ($request) {
                if ($name = $request->input('name')) {
                    $query->where(['name' => $name]);
                }
                if ($guard_name = $request->input('guard_name')) {
                    $query->where(['guard_name' => $guard_name]);
                }
            })
                ->orderBy($this->getOrderByColumn(), $this->getOrderByDirection())
                ->paginate($this->getPageSize())
                ->toArray()
        );
    }

    public function store(PermissionStoreRequest $request)
    {
        $validated = $request->validated();

        if ($id = $this->request->input('id')) {
            $permission = Permission::findOrFail($id);
        } else {
            $permission = new Permission();
        }

        $permission->fill($validated);
        $permission->save();

        return $this->response;
    }

    public function options()
    {
        $request = $this->request;
        return $this->response->json(
            Permission::where(function ($query) use ($request) {
                if ($guard_name = $request->input('guard_name')) {
                    $query->where(['guard_name' => $guard_name]);
                }
            })
                ->select(['id', 'name', 'alias'])->get()->toArray()
        );
    }
}
