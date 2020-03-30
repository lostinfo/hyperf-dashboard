<?php

declare(strict_types=1);

namespace App\Request\Admin;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class AdminStoreRequest extends FormRequest
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
        $id    = $this->input('id');
        $rules = [
            'username' => [
                'required',
                Rule::unique('admins')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '<>', $id);
                    }
                })
            ],
            'active'   => 'required|boolean',
            'roles'    => 'required|array',
        ];
        if (!$id) {
            $rules['password'] = 'required';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'active'   => '是否激活',
            'roles'    => '角色',
        ];
    }
}
