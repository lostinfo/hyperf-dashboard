<?php
/**
 * Created by PhpStorm.
 * User: wei gao
 * Email:1225039937@qq.com
 * Date: 2020-03-27
 * Time: 10:42
 */

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Request\Admin\ImageUploadRequest;
use Hyperf\Utils\Str;

class FileController extends AbstractController
{
    public function article(ImageUploadRequest $request)
    {
        $file_name    = Str::random(40);
        $extension = $request->file('file')->getExtension();

        $url = "/article/{$file_name}.{$extension}";
        $request->file('file')->moveTo(BASE_PATH.'/public'.$url);

        return $this->response->json([
            'url'          => $url,
            'absolute_url' => config('app_url').$url,
        ]);
    }
}
