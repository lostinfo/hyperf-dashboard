<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;


use App\Model\Admin;

class IndexController extends AbstractController
{
    public function index()
    {
        $admin = Admin::findOrFail(1);
        return $admin;
    }
}
