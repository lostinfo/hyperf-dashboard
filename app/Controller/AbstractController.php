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

use App\Support\ResponseHelper;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param string $message
     * @param int $status_code
     * @return mixed
     */
    public function responseMsg(string $message, $status_code = 422)
    {
        return $this->response->withStatus($status_code)->withBody(ResponseHelper::createBody($message));
    }

    protected function getOrderByColumn()
    {
        return $this->request->input('order_by_column', 'id');
    }

    protected function getOrderByDirection()
    {
        return $this->request->input('order_by_direction', 'desc');
    }

    protected function getPageSize()
    {
        return (int)($this->request->input('page_size', 15));
    }
}
