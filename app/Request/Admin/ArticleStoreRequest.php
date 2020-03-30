<?php

declare(strict_types=1);

namespace App\Request\Admin;

use Hyperf\Validation\Request\FormRequest;

class ArticleStoreRequest extends FormRequest
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
        return [
            'title'    => 'required',
            'subtitle' => 'required',
            'poster'   => 'required',
            'can_show' => 'required|bool',
            'is_index' => 'required|bool',
            'markdown' => 'required',
            'content'  => 'required',
            'sort'     => 'required|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'    => '标题',
            'subtitle' => '副标题',
            'poster'   => '封面',
            'can_show' => '是否显示',
            'is_index' => '首页推荐',
            'markdown' => '正文',
            'content'  => '正文',
            'sort'     => '排序',
        ];
    }
}
