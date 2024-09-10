<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Controller\AdminApi;

use App\Constants\CommonEnum;
use BadMethodCallException;
use crmeb\utils\Arr;
use Illuminate\Routing\Controller;

/**
 * 基础控制器
 * Class AuthController.
 * @property string $uuid 登录用户UUID
 * @property bool $isEnt 是否登录企业
 * @property array $userInfo 登录工作台用户信息
 * @property int $entId 当前工作台企业ID
 * @property array $entInfo 当前工作台公司信息
 */
class AuthController extends Controller
{
    public string $origin = CommonEnum::ORIGIN_WEB;

    /**
     * @var string[]
     */
    protected $rule = [
        'uuid'     => 'uuId',
        'isEnt'    => 'isEnt',
        'entInfo'  => 'entInfo',
        'entId'    => 'entId',
        'userInfo' => 'userInfo',
    ];

    /**
     * @var
     */
    protected $service;

    /**
     * @var \Illuminate\Contracts\Foundation\Application|mixed|\request|(\request&\Illuminate\Contracts\Foundation\Application)
     */
    public mixed $request;
    /**
     * @var \Illuminate\Contracts\Foundation\Application|\Lauthz\EnforcerManager|(\Lauthz\EnforcerManager&\Illuminate\Contracts\Foundation\Application)|mixed
     */
    public mixed $enforcer;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->request  = app('request');
        $this->enforcer = app('enforcer');
        if (! $this->request->hasMacro('postMore')) {
            $this->request->macro('postMore', function (array $params, bool $suffix = false) {
                return Arr::more(app()->request, $params, $suffix);
            });
        }
        if (! $this->request->hasMacro('getMore')) {
            $this->request->macro('getMore', function (array $params, bool $suffix = false) {
                return Arr::more(app()->request, $params, $suffix, 'query');
            });
        }
    }

    /**
     * 失败返回.
     * @param null $message
     * @return mixed
     */
    public function fail($message = null, array $data = [], array $replace = [], int $tips = 1)
    {
        return app('json')->fail($message, $data, $replace, $tips);
    }

    /**
     * 成功返回.
     * @param null $message
     * @return mixed
     */
    public function success($message = null, array $data = [], array $replace = [], int $tips = 1)
    {
        return app('json')->success($message, $data, $replace, $tips);
    }

    /**
     * 获取属性.
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, array_keys($this->rule))) {
            return $this->request->{$this->rule[$name]}();
        }
        throw new BadMethodCallException(__('common.empty.property', ['name' => $name]));
    }
}
