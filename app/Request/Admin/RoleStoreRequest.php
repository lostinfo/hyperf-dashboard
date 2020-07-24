<?php

declare(strict_types=1);

namespace App\Request\Admin;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class RoleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->input('id');
        return [
            'name'        => [
                'required',
                Rule::unique('roles')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '<>', $id);
                    }
                })
            ],
            'guard_name'  => [
                'required',
                Rule::in(array_keys(config('auth.guards'))),
            ],
            'permissions' => 'required|array',
            'menus'       => 'required|array',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => '名称',
            'guard_name'  => '用户组',
            'permissions' => '权限',
            'menus'       => '菜单',
        ];
    }
}
