<?php
/**
 * This file is part of Notadd.
 *
 * @author AllenGu <674397601@qq.com>
 * @copyright (c) 2017, iBenchu.org
 * @datetime 2017-05-22 16:24
 */
namespace Notadd\Multipay\Listeners;

use Notadd\Foundation\Routing\Abstracts\RouteRegister as AbstractRouteRegister;
use Notadd\Multipay\Controllers\AlipayController;
use Notadd\Multipay\Controllers\PayController;
use Notadd\Multipay\Controllers\QueryController;
use Notadd\Multipay\Controllers\UploadController;
use Notadd\Multipay\Controllers\WechatController;

/**
 * Class RouteRegister.
 */
class RouteRegister extends AbstractRouteRegister
{
    /**
     * Handle Route Registrar.
     */
    public function handle()
    {

        $this->router->group(['middleware' => ['cross', 'web'], 'prefix' => 'api/multipay'], function () {
            $this->router->get('pay', PayController::class. '@pay');
            $this->router->get('query', PayController::class. '@query');
            $this->router->get('refund', PayController::class. '@refund');
            $this->router->get('cancel', PayController::class. '@cancel');
            $this->router->get('test', PayController::class. '@test');
            $this->router->get('order', QueryController::class.'@list');
        });
        $this->router->get('upload', UploadController::class. '@upload');
        $this->router->get('webnotify', PayController::class. '@webNotify');
        $this->router->post('execute', UploadController::class. '@execute');

        $this->router->group(['middleware' => ['cross', 'web'], 'prefix' => 'api/multipay/alipay'], function () {
            $this->router->post('set',AlipayController::class.'@set');
            $this->router->post('get',AlipayController::class.'@get');
            $this->router->get('order',AlipayController::class.'@order');
        });

        $this->router->group(['middleware' => ['cross', 'web'], 'prefix' => 'api/multipay/wechat'], function () {
            $this->router->post('set',WechatController::class.'@set');
            $this->router->post('get',WechatController::class.'@get');
            $this->router->get('order',WechatController::class.'@order');

        });

    }
}
