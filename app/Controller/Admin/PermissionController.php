<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\Permission;
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
            })->orderBy(
                $request->input('order_by_column', 'id'),
                $request->input('order_by_direction', 'desc')
            )->paginate((int)($request->input('page_size', 15)))
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
        return $this->response->json(
            Permission::select(['id', 'action', 'name'])->get()->toArray()
        );
    }
}
